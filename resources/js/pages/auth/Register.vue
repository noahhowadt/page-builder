<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
</script>

<template>
    <AuthBase
        title="Registration unavailable"
        description="This application uses an invite-free setup flow instead of self-service registration."
    >
        <Head title="Registration unavailable" />

        <div class="space-y-4 text-center text-sm text-muted-foreground">
            <p>
                Self-service registration is disabled for this CMS. Use the setup flow to create the
                first account, then sign in here.
            </p>

            <div>
                <TextLink :href="login()" class="underline underline-offset-4">Go to login</TextLink>
            </div>
        </div>
    </AuthBase>
</template>
