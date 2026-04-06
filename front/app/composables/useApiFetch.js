export function useApiFetch(path, options = {}, useFetchFunction = false) {
  const { $i18n } = useNuxtApp();
  const config = useRuntimeConfig();
  const token = useCookie('token', { default: () => null });

  const headers = {
    ...options?.headers,
    accept: 'application/json',
    'X-App-Language': $i18n.locale.value,
  };

  if (token.value) {
    headers.Authorization = `Bearer ${token.value}`;
  }

  const key = options.key || `${path}-${$i18n.locale.value}`;

  return useFetchFunction
    ? useFetch(path, {
        baseURL: config.public.apiBaseUrl,
        watch: false,
        key,
        ...options,
        headers,
      })
    : $fetch(path, {
        ...options,
        baseURL: config.public.apiBaseUrl,
        headers,
      });
}
