<?= '<?xml version="1.0" encoding="UTF-8"?>' . "\n" ?>
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
    <channel>
        <title>{{ $store->name }}</title>
        <link>{{ $store->url }}</link>
        <description>{{ $store->description }}</description>

        @foreach ($products as $product)
            <item>
                <g:id>{{ app()->getLocale() . '-' . $product->id }}</g:id>
                <g:title>{{ $product->name }}</g:title>
                <g:description>{{ $product->description }}</g:description>
                <g:link>{{ route("services.show", ["service" => $product->id]) }}</g:link>
                <g:image_link>{{ app()->isLocal() ? "https://picsum.photos/400?random=" . $product->id : $product->getMedia("image")->first()?->getUrl("sm") }}</g:image_link>
                <g:availability>in stock</g:availability>
                <g:price>{{ $product->price }} {{ __("ui.currency", [], "en") }}</g:price>
                <g:condition>new</g:condition>
                <g:google_product_category>536</g:google_product_category>
                <g:product_type>{{ $product->category?->parent?->name }} > {{ $product->category?->name }}</g:product_type>
            </item>
        @endforeach

    </channel>
</rss>
