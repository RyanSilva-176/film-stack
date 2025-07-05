<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import ToastContainer from '@/components/ui/ToastContainer.vue';
import { useToast } from '@/composables/useToastSystem';
import FilmStackAuthLayoutReverse from '@/layouts/auth/FilmStackAuthLayoutReverse.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const { success, error } = useToast();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onSuccess: () => {
            success('Conta criada', 'Sua conta foi criada com sucesso! Bem-vindo ao FilmStack!');
        },
        onError: () => {
            if (Object.keys(form.errors).length > 0) {
                error('Erro no cadastro', 'Verifique os dados informados e tente novamente.');
            }
        },
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const handleSocialLogin = (provider: string) => {
    window.location.href = `/auth/${provider}/redirect`;
};
</script>

<template>
    <Head title="Criar Conta - FilmStack" />

    <FilmStackAuthLayoutReverse title="Crie sua conta" description="Registre-se gratuitamente para começar a organizar seus filmes favoritos">
        <!-- Error Messages -->
        <div v-if="form.hasErrors" class="mb-4 rounded-lg border border-red-500/30 bg-red-900/20 p-4">
            <div v-for="(error, key) in form.errors" :key="key" class="text-sm text-red-400">
                {{ error }}
            </div>
        </div>

        <!-- Registration Form -->
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Name Field -->
            <div class="space-y-2">
                <label for="name" class="block text-sm font-medium text-gray-300">Nome Completo</label>
                <input
                    id="name"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    v-model="form.name"
                    placeholder="Seu nome"
                    class="w-full rounded-lg border border-gray-700 bg-gray-900/50 px-4 py-3 text-white placeholder-gray-500 transition-all duration-200 focus:border-red-500 focus:bg-gray-900 focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                />
                <p v-if="form.errors.name" class="text-sm text-red-400">
                    {{ form.errors.name }}
                </p>
            </div>

            <!-- Email Field -->
            <div class="space-y-2">
                <label for="email" class="block text-sm font-medium text-gray-300">Email</label>
                <input
                    id="email"
                    type="email"
                    required
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
                <label for="password" class="block text-sm font-medium text-gray-300">Senha</label>
                <input
                    id="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    v-model="form.password"
                    placeholder="••••••••"
                    class="w-full rounded-lg border border-gray-700 bg-gray-900/50 px-4 py-3 text-white placeholder-gray-500 transition-all duration-200 focus:border-red-500 focus:bg-gray-900 focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                />
                <p v-if="form.errors.password" class="text-sm text-red-400">
                    {{ form.errors.password }}
                </p>
            </div>

            <!-- Confirm Password Field -->
            <div class="space-y-2">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-300">Confirmar Senha</label>
                <input
                    id="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    v-model="form.password_confirmation"
                    placeholder="••••••••"
                    class="w-full rounded-lg border border-gray-700 bg-gray-900/50 px-4 py-3 text-white placeholder-gray-500 transition-all duration-200 focus:border-red-500 focus:bg-gray-900 focus:ring-2 focus:ring-red-500/20 focus:outline-none"
                />
                <p v-if="form.errors.password_confirmation" class="text-sm text-red-400">
                    {{ form.errors.password_confirmation }}
                </p>
            </div>

            <!-- Submit Button -->
            <Button
                type="submit"
                variant="primary"
                label="Criar Conta"
                icon="user-plus"
                full-width
                size="lg"
                :loading="form.processing"
                :disabled="form.processing"
            />

            <!-- Social Login Divider -->
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-700"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="bg-black px-4 text-gray-400">ou continue com</span>
                </div>
            </div>

            <!-- Google Login -->
            <Button type="button" variant="outline" full-width size="lg" @click="handleSocialLogin('google')">
                <span class="flex items-center justify-center text-white">
                    <font-awesome-icon :icon="['fab', 'google']" class="mr-2 h-5 w-5" />
                    Continuar com Google
                </span>
            </Button>

            <!-- Login Link -->
            <div class="text-center">
                <p class="text-sm text-gray-400">
                    Já tem uma conta?
                    <Link :href="route('login')" class="font-medium text-red-400 transition-colors hover:text-red-300"> Fazer login </Link>
                </p>
            </div>
        </form>

        <!-- Toast Notifications -->
        <ToastContainer />
    </FilmStackAuthLayoutReverse>
</template>
