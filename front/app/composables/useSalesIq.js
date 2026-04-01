export function useSalesIq() {
  function openChat() {
    if (window.$zoho?.salesiq) {
      $zoho.salesiq.floatwindow.visible('show');
      $zoho.salesiq.chatwindow.open();
    }
  }

  return { openChat };
}
