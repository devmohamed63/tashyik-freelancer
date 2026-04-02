import { proxyRequest } from 'h3';

export default defineEventHandler(async (event) => {
  const url = new URL(event.node.req.url, 'http://localhost');
  const path = url.pathname.replace(/^\/__api/, '') + url.search;
  const target = `http://127.0.0.1:8000${path}`;

  return proxyRequest(event, target, {
    headers: {
      Host: 'api.localhost:8000',
    },
  });
});
