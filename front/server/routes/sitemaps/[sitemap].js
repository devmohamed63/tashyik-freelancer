export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig();

  event.node.res.setHeader('Content-Type', 'application/xml');

  try {
    const file = await $fetch(`${config.public.apiBaseUrl}/sitemaps/${event.context.params.sitemap}`, {
      headers: {
        Accept: 'application/json',
      },
    });

    return file;
  } catch (error) {
    throw createError({
      statusCode: 404,
      message: 'Page Not Found',
      fatal: true,
    });
  }
});
