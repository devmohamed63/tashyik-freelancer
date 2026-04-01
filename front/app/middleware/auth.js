export default defineNuxtRouteMiddleware(async (to, from) => {
  const auth = useAuthStore();
  const localePath = useLocalePath();

  await auth.fetchUser();

  if (!auth.user) {
    return navigateTo(localePath({ name: 'login', query: { redirect: to.path } }));
  }
});
