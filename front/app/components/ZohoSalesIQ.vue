<script setup>
  const config = useRuntimeConfig();
  const key = config.public?.salesIqKey;

  if (key) {
    useHead({
      script: [
        {
          innerHTML: `
          window.$zoho = window.$zoho || {};
          $zoho.salesiq = $zoho.salesiq || { ready: function () {} };
        `,
        },
        {
          src: `https://salesiq.zohopublic.sa/widget?wc=${key}`,
          defer: true,
        },
      ],
    });
  }
</script>
