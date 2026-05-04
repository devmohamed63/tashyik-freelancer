<script setup>
  const props = defineProps({
    latitude: {
      type: Number,
      required: false,
    },
    longitude: {
      type: Number,
      required: false,
    },
  });

  const emit = defineEmits(['change']);
  const map = ref(null);
  const loader = ref(true);
  // Override global scripts.defaultScriptOptions (idle deferral for GTM): load Maps API on Nuxt ready on address pages only.
  const { onLoaded } = useScriptGoogleMaps({
    scriptOptions: {
      trigger: 'onNuxtReady',
    },
  });

  const position = ref({
    latitude: null,
    longitude: null,
    address: null,
  });

  onMounted(() => {
    onLoaded(async (instance) => {
      const maps = await instance.maps;
      const geocoder = new maps.Geocoder();

      const fetchAddress = (lat, lng) => {
        geocoder.geocode({ location: { lat, lng } }, (results, status) => {
          if (status === 'OK' && results[0]) {
            position.value = {
              latitude: lat,
              longitude: lng,
              address: results[0].formatted_address,
            };
          } else {
            position.value = {
              latitude: lat,
              longitude: lng,
            };
            console.error('Geocoder failed due to: ' + status);
          }
        });
      };

      const mapInstance = new maps.Map(map.value, {
        center: { lat: 24.692744, lng: 46.7276403 },
        zoom: 12,
        zoomControl: true,
        gestureHandling: 'greedy',
      });

      mapInstance.addListener('idle', () => {
        const center = mapInstance.getCenter();

        fetchAddress(center.lat(), center.lng());
      });

      // Get user location
      if (props.latitude && props.longitude) {
        const userPosition = {
          lat: parseFloat(props.latitude),
          lng: parseFloat(props.longitude),
        };

        mapInstance.setCenter(userPosition);
        mapInstance.setZoom(19);

        fetchAddress(userPosition.lat, userPosition.lng);
      } else if (navigator.geolocation) {
        // Get current location
        navigator.geolocation.getCurrentPosition(
          (position) => {
            const currentPosition = {
              lat: position.coords.latitude,
              lng: position.coords.longitude,
            };

            mapInstance.setCenter(currentPosition);
            mapInstance.setZoom(19);

            fetchAddress(currentPosition.lat, currentPosition.lng);
          },
          (error) => {
            console.error('Failed to get user location:', error);
          },
        );
      }

      loader.value = false;
    });
  });

  watch(
    () => position,
    () => {
      emit('change', position);
    },
    {
      deep: true,
    },
  );
</script>

<template>
  <div :class="$attrs.class" class="relative rounded-xl overflow-hidden">
    <div v-if="loader" class="absolute bg-gray-100 w-full h-full flex items-center justify-center">
      <AppSpinner class="w-8 h-8" />
    </div>
    <div class="w-full h-full" ref="map"></div>
    <div v-if="!loader" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-full text-3xl pointer-events-none text-brand-500">
      <!-- mdi:map-marker -->
      <svg class="w-12 h-12 stroke-white" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24">
        <!-- Icon from Material Design Icons by Pictogrammers - https://github.com/Templarian/MaterialDesign/blob/master/LICENSE -->
        <path fill="currentColor" d="M12 11.5A2.5 2.5 0 0 1 9.5 9A2.5 2.5 0 0 1 12 6.5A2.5 2.5 0 0 1 14.5 9a2.5 2.5 0 0 1-2.5 2.5M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7" />
        <circle class="fill-brand-600" cx="12" cy="9" r="2.5" />
      </svg>
    </div>
  </div>
</template>
