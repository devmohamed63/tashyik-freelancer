export const useLayoutStore = defineStore('layout', () => {
  const auth = useAuthStore();
  const logo = ref(null);
  const description = ref(null);
  const mobile_app_links = ref(null);
  const contact_info = ref({});
  const social_links = ref({});

  async function load() {
    try {
      const response = await useApiFetch('/general/layout');

      auth.user = response.user;
      logo.value = response.logo;
      description.value = response.description;
      mobile_app_links.value = response.mobile_app_links;
      contact_info.value = response.contact_info;
      social_links.value = response.social_links;
    } catch (error) {
      console.error('Failed to fetch layout:', error);
    }
  }

  return {
    // state
    logo,
    description,
    mobile_app_links,
    contact_info,
    social_links,

    // actions
    load,
  };
});
