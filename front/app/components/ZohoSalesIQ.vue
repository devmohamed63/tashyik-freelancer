<script setup>
  import { onMounted } from 'vue';

  const config = useRuntimeConfig();
  const key = config.public?.salesIqKey;

  const ZOHO_SRC_ID = 'zoho-salesiq-src';
  const ZOHO_BOOT_ID = 'zoho-salesiq-boot';

  const injectZohoScript = () => {
    if (!key || typeof window === 'undefined' || typeof document === 'undefined') return;
    if (document.getElementById(ZOHO_SRC_ID)) return;

    const boot = document.createElement('script');
    boot.id = ZOHO_BOOT_ID;
    boot.text = 'window.$zoho=window.$zoho||{};$zoho.salesiq=$zoho.salesiq||{ready:function(){}};';
    document.head.appendChild(boot);

    const script = document.createElement('script');
    script.id = ZOHO_SRC_ID;
    script.src = `https://salesiq.zohopublic.sa/widget?wc=${key}`;
    script.async = true;
    document.head.appendChild(script);
  };

  onMounted(() => {
    if (!key) return;

    // Load third-party chat after first interaction or idle time to reduce main-thread work on initial render.
    const loadOnce = () => {
      injectZohoScript();
      window.removeEventListener('pointerdown', loadOnce);
      window.removeEventListener('keydown', loadOnce);
      window.removeEventListener('scroll', loadOnce);
    };

    window.addEventListener('pointerdown', loadOnce, { once: true, passive: true });
    window.addEventListener('keydown', loadOnce, { once: true });
    window.addEventListener('scroll', loadOnce, { once: true, passive: true });

    if ('requestIdleCallback' in window) {
      window.requestIdleCallback(loadOnce, { timeout: 4000 });
    } else {
      window.setTimeout(loadOnce, 4000);
    }
  });
</script>

<template>
  <!-- Head-only integration component -->
  <span class="hidden" aria-hidden="true"></span>
</template>
