<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderExtraPaid;
use App\Events\OrderPaid;
use App\Events\PlanPaid;
use App\Utils\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends ApiController
{
    private function verifyPaymobHmac(Request $request)
    {
        $hmacSecret = env('PAYMOB_HMAC');
        $data = $request->all();

        abort_if(! isset($data['obj']), 403);

        $order = $data['obj'];

        $concatenatedString =
            $order['amount_cents'].
            $order['created_at'].
            $order['currency'].
            ($order['error_occured'] ? 'true' : 'false').
            ($order['has_parent_transaction'] ? 'true' : 'false').
            $order['id'].
            $order['integration_id'].
            ($order['is_3d_secure'] ? 'true' : 'false').
            ($order['is_auth'] ? 'true' : 'false').
            ($order['is_capture'] ? 'true' : 'false').
            ($order['is_refunded'] ? 'true' : 'false').
            ($order['is_standalone_payment'] ? 'true' : 'false').
            ($order['is_voided'] ? 'true' : 'false').
            $order['order']['id'].
            $order['owner'].
            ($order['pending'] ? 'true' : 'false').
            $order['source_data']['pan'].
            $order['source_data']['sub_type'].
            $order['source_data']['type'].
            ($order['success'] ? 'true' : 'false');

        $calculatedHmac = hash_hmac('sha512', $concatenatedString, $hmacSecret);

        return $calculatedHmac === $data['hmac'];
    }

    public function paymob(Request $request)
    {
        if ($request->type == 'TRANSACTION' && $request->obj['success']) {
            abort_if(! $this->verifyPaymobHmac($request), 403, 'Invalid HMAC');

            try {
                $transactionId = $request->obj['id'];
                $data = $request->obj['payment_key_claims']['extra']['reference'];

                switch ($data['type']) {
                    case 'order_paid':
                        OrderPaid::dispatch($data);
                        break;

                    case 'order_extra_paid':
                        OrderExtraPaid::dispatch($data);
                        break;

                    case 'plan_paid':
                        PlanPaid::dispatch($data);
                        break;
                }
            } catch (\Throwable $th) {
                Log::error("Paymob webhook failed: \n$th \n$request", [
                    'trace' => $th->getTraceAsString(),
                ]);
                throw $th;
            }

            return response()->noContent();
        } else {
            abort(403);
        }
    }
}
