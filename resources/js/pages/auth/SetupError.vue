<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-50 px-4">
    <div
      class="bg-white border border-gray-200 rounded-lg shadow-sm p-8 max-w-lg w-full text-center"
    >
      <h1 class="text-2xl font-semibold text-gray-900 mb-4">
        {{ title }}
      </h1>

      <p class="text-gray-700 mb-6">
        {{ message }}
      </p>

      <div v-if="reason === 'locked'" class="text-sm text-gray-500">
        <p>
          Please add a
          <code class="bg-gray-100 px-1 py-0.5 rounded text-gray-800"
            >SETUP_TOKEN</code
          >
          to your
          <code class="bg-gray-100 px-1 py-0.5 rounded text-gray-800">.env</code>
          file and reload, or create a user directly in the database.
        </p>
      </div>

      <div v-else-if="reason === 'weak_token'" class="text-sm text-gray-500">
        <p>
          Your
          <code class="bg-gray-100 px-1 py-0.5 rounded text-gray-800"
            >SETUP_TOKEN</code
          >
          is too short or insecure. Please generate a strong token (at least 32
          characters).
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  reason: {
    type: String,
    required: true,
    validator: (val) => ["locked", "weak_token"].includes(val),
  },
});

const title = computed(() => {
  switch (props.reason) {
    case "locked":
      return "CMS Locked";
    case "weak_token":
      return "Weak Setup Token";
    default:
      return "Setup Error";
  }
});

const message = computed(() => {
  switch (props.reason) {
    case "locked":
      return "No users exist and no setup token is configured.";
    case "weak_token":
      return "The provided setup token does not meet security requirements.";
    default:
      return "An unknown setup error occurred.";
  }
});
</script>