<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import ToastContainer from '@/components/ui/ToastContainer.vue';
import { useToast } from '@/composables/useToastSystem';
import FilmStackAuthLayout from '@/layouts/auth/FilmStackAuthLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    status?: string;
    canResetPassword: boolean;
    hasGoogleAuth: boolean;
}>();

const { success, error } = useToast();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onSuccess: () => {
            success('Login realizado', 'Bem-vindo de volta ao FilmStack!');
        },
        onError: () => {
            if (form.errors.email || form.errors.password) {
                error('Erro no login', 'Verifique suas credenciais e tente novamente.');
            }
        },
        onFinish: () => form.reset('password'),
    });
};

const handleSocialLogin = (provider: string) => {
    window.location.href = `/auth/${provider}/redirect`;
};
</script>

<template>
    <Head title="Entrar - FilmStack" />

    <FilmStackAuthLayout title="Bem-vindo de volta" description="Entre na sua conta para continuar organizando seus filmes favoritos">
        <!-- Status Message -->
        <div v-if="props.status" class="mb-6 rounded-lg border border-green-500/30 bg-green-900/20 p-4 text-center">
            <p class="text-sm font-medium text-green-400">{{ props.status }}</p>
        </div>

        <!-- Error Messages -->
        <div v-if="form.hasErrors" class="mb-4 rounded-lg border border-red-500/30 bg-red-900/20 p-4">
            <div v-for="(error, key) in form.errors" :key="key" class="text-sm text-red-400">
                {{ error }}
            </div>
        </div>

        <!-- Login Form -->
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Email Field -->
            <div class="space-y-2">
                <label for="email" class="block text-sm font-medium text-gray-300"> Email </label>
                <input
                    id="email"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
                    v-model="form.email"
                    placeholder="seu@email.com"
                    class="w-full rounded-lg border border-gray-700 bg-gray-900/50 px-4 py-3 text-white placeholder-gray-500 transition-all duration-200 focus:border-red-500 focus:bg-gray-900 focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                />
                <p v-if="form.errors.email" class="text-sm text-red-400">
                    {{ form.errors.email }}
                </p>
            </div>

            <!-- Password Field -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-sm font-medium text-gray-300"> Senha </label>
                    <Link
                        v-if="props.canResetPassword"
                        :href="route('password.request')"
                        class="text-sm text-red-400 transition-colors hover:text-red-300"
                    >
                        Esqueceu a senha?
                    </Link>
                </div>
                <input
                    id="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    v-model="form.password"
                    placeholder="••••••••"
                    class="w-full rounded-lg border border-gray-700 bg-gray-900/50 px-4 py-3 text-white placeholder-gray-500 transition-all duration-200 focus:border-red-500 focus:bg-gray-900 focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                />
                <p v-if="form.errors.password" class="text-sm text-red-400">
                    {{ form.errors.password }}
                </p>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label class="flex cursor-pointer items-center space-x-3">
                    <input
                        type="checkbox"
                        v-model="form.remember"
                        class="h-4 w-4 rounded border-gray-700 bg-gray-900 text-red-500 focus:ring-red-500 focus:ring-offset-0"
                    />
                    <span class="text-sm text-gray-300">Lembrar de mim</span>
                </label>
            </div>

            <!-- Submit Button -->
            <Button
                type="submit"
                variant="primary"
                label="Entrar"
                icon="sign-in-alt"
                full-width
                size="lg"
                :loading="form.processing"
                :disabled="form.processing"
            />

            <!-- Social Login Divider -->
            <div v-if="props.hasGoogleAuth" class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-700"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="bg-black px-4 text-gray-400">ou continue com</span>
                </div>
            </div>

            <!-- Google Login -->
            <Button v-if="props.hasGoogleAuth" type="button" variant="outline" full-width size="lg" @click="handleSocialLogin('google')">
                <span class="flex items-center justify-center text-white">
                    <font-awesome-icon :icon="['fab', 'google']" class="mr-2 h-5 w-5" />
                    Continuar com Google
                </span>
            </Button>

            <!-- Sign Up Link -->
            <div class="text-center">
                <p class="text-sm text-gray-400">
                    Não tem uma conta?
                    <Link :href="route('register')" class="font-medium text-red-400 transition-colors hover:text-red-300">
                        Criar conta gratuita
                    </Link>
                </p>
            </div>
        </form>

        <!-- Toast Notifications -->
        <ToastContainer />
    </FilmStackAuthLayout>
</template>
