export function useAuthToken() {
  const token = useCookie('token', { default: () => null });

  function setToken(newToken) {
    token.value = newToken;
  }

  function clearToken() {
    token.value = null;
  }

  return { token, setToken, clearToken };
}
