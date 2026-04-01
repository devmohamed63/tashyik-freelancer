export const useCitiesStore = defineStore('cities', () => {
  const collection = ref(null);

  async function load() {
    if (!collection.value) {
      try {
        const response = await useApiFetch('/cities');

        collection.value = response.data;
      } catch (error) {
        console.error('Failed to get cities:', error);
      }
    }
  }

  return {
    // state
    collection,

    // actions
    load,
  };
});
