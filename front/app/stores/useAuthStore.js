export const useAuthStore = defineStore('auth', () => {
  const { setToken, clearToken } = useAuthToken();
  const localePath = useLocalePath();
  const route = useRoute();
  const user = ref(null);
  const isLoggedIn = computed(() => !!user.value);

  async function register(credentials) {
    try {
      const response = await useApiFetch('/register', {
        method: 'POST',
        body: credentials,
      });

      user.value = response.data;

      setToken(response.token);

      return navigateTo(route.query.redirect ?? localePath({ name: 'index' }));
    } catch (error) {
      // Check for validation errors
      if (error.statusCode === 422) {
        return error.data.errors.errors;
      } else {
        console.error('Failed to register:', error);
      }
    }
  }

  async function login(credentials) {
    resetUser();

    try {
      const response = await useApiFetch('/login', {
        method: 'POST',
        body: credentials,
      });

      user.value = response.data;

      setToken(response.token);

      return navigateTo(route.query.redirect ?? localePath({ name: 'index' }));
    } catch (error) {
      // Check for validation errors
      if (error.statusCode === 422) {
        return error.data.errors.errors;
      } else {
        console.error('Failed to login:', error);
      }
    }
  }

  async function fetchUser() {
    try {
      const response = await useApiFetch('/user');

      user.value = response.data;
    } catch (error) {
      if (error.statusCode != 401) {
        console.error('Failed to fetch user:', error);
      }
    }
  }

  async function logout() {
    try {
      await useApiFetch('/logout', {
        method: 'POST',
      });

      await resetUser();

      return navigateTo(localePath({ name: 'index' }));
    } catch (error) {
      console.error('Failed to logout:', error);
    }
  }

  async function resetUser() {
    user.value = null;

    await clearToken();
  }

  return {
    // state
    user,
    isLoggedIn,

    // actions
    register,
    login,
    resetUser,
    logout,
    fetchUser,
  };
});
