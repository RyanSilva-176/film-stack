<template>
    <div class="min-h-screen bg-black p-8 text-white">
        <div class="mx-auto max-w-4xl space-y-12">
            <!-- Header -->
            <div class="text-center">
                <h1 class="mb-4 text-4xl font-bold">Film<span class="text-red-500">Stack</span> Button & Toast System</h1>
                <p class="text-gray-400">Sistema unificado de botões e notificações para consistência visual</p>
            </div>

            <!-- Variantes Principais -->
            <section>
                <h2 class="mb-6 text-2xl font-bold text-red-500">🎨 Variantes de Botões</h2>
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                    <div class="space-y-3">
                        <h3 class="text-sm font-medium text-gray-300">Primary</h3>
                        <Button variant="primary" label="Ver Detalhes" icon="info-circle" />
                    </div>

                    <div class="space-y-3">
                        <h3 class="text-sm font-medium text-gray-300">Secondary</h3>
                        <Button variant="secondary" label="Salvar" icon="bookmark" />
                    </div>

                    <div class="space-y-3">
                        <h3 class="text-sm font-medium text-gray-300">Glass</h3>
                        <Button variant="glass" label="Minha Lista" icon="plus" />
                    </div>

                    <div class="space-y-3">
                        <h3 class="text-sm font-medium text-gray-300">Outline</h3>
                        <Button variant="outline" label="Cancelar" />
                    </div>

                    <div class="space-y-3">
                        <h3 class="text-sm font-medium text-gray-300">Ghost</h3>
                        <Button variant="ghost" label="Pular" />
                    </div>

                    <div class="space-y-3">
                        <h3 class="text-sm font-medium text-gray-300">Destructive</h3>
                        <Button variant="destructive" label="Excluir" icon="trash" />
                    </div>

                    <div class="space-y-3">
                        <h3 class="text-sm font-medium text-gray-300">Success</h3>
                        <Button variant="success" label="Confirmar" icon="check" />
                    </div>
                </div>
            </section>

            <!-- Sistema de Toast -->
            <section>
                <h2 class="mb-6 text-2xl font-bold text-red-500">🔔 Sistema de Toast</h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-4 rounded-lg bg-gray-900 p-6">
                        <h3 class="text-lg font-medium">Tipos de Notificação</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <Button
                                variant="success"
                                label="Sucesso"
                                icon="check-circle"
                                @click="() => success('Operação realizada!', 'Filme adicionado à sua lista')"
                            />
                            <Button
                                variant="destructive"
                                label="Erro"
                                icon="exclamation-circle"
                                @click="() => error('Erro detectado', 'Falha na conexão com o servidor')"
                            />
                            <Button
                                variant="primary"
                                label="Info"
                                icon="info-circle"
                                @click="() => info('Informação', 'Processando dados do filme...')"
                            />
                            <Button
                                variant="secondary"
                                label="Aviso"
                                icon="exclamation-triangle"
                                @click="() => warning('Atenção', 'Alguns dados podem estar desatualizados')"
                            />
                        </div>
                    </div>

                    <div class="space-y-4 rounded-lg bg-gray-900 p-6">
                        <h3 class="text-lg font-medium">Demonstrações Avançadas</h3>
                        <div class="space-y-3">
                            <Button variant="glass" label="Múltiplos Toasts" icon="copy" full-width @click="showMultipleToasts" />
                            <Button variant="outline" label="Toast Persistente" icon="clock" full-width @click="showPersistentToast" />
                            <Button variant="ghost" label="Limpar Todos" icon="times" full-width @click="clearAll" />
                        </div>
                    </div>
                </div>
            </section>

            <!-- Demo Interativo -->
            <section class="mb-4">
                <h2 class="mb-6 text-2xl font-bold text-red-500">🧪 Demo Interativo</h2>
                <div class="rounded-lg bg-gray-900 p-6">
                    <h3 class="mb-6 text-lg font-medium">Personalize um Botão</h3>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="space-y-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-300">Variante</label>
                                <select v-model="demoVariant" class="w-full rounded bg-gray-800 p-2 text-white">
                                    <option value="primary">Primary</option>
                                    <option value="secondary">Secondary</option>
                                    <option value="glass">Glass</option>
                                    <option value="outline">Outline</option>
                                    <option value="ghost">Ghost</option>
                                    <option value="destructive">Destructive</option>
                                    <option value="success">Success</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-300">Tamanho</label>
                                <select v-model="demoSize" class="w-full rounded bg-gray-800 p-2 text-white">
                                    <option value="xs">XS</option>
                                    <option value="sm">Small</option>
                                    <option value="md">Medium</option>
                                    <option value="lg">Large</option>
                                    <option value="xl">Extra Large</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-300">Ícone</label>
                                <select v-model="demoIcon" class="w-full rounded bg-gray-800 p-2 text-white">
                                    <option value="">Sem ícone</option>
                                    <option value="star">Star</option>
                                    <option value="heart">Heart</option>
                                    <option value="play">Play</option>
                                    <option value="film">Film</option>
                                    <option value="info-circle">Info</option>
                                    <option value="check">Check</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-300">Estado</label>
                                <select v-model="demoState" class="w-full rounded bg-gray-800 p-2 text-white">
                                    <option value="normal">Normal</option>
                                    <option value="loading">Loading</option>
                                    <option value="disabled">Disabled</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-6 flex items-center gap-4">
                            <label class="flex items-center gap-2">
                                <input v-model="demoFullWidth" type="checkbox" class="rounded" />
                                <span class="text-sm">Largura Total</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 rounded-lg bg-black p-4">
                        <h4 class="mb-4 text-sm font-medium">Preview:</h4>
                        <Button
                            :variant="demoVariant"
                            :size="demoSize"
                            :icon="demoIcon || undefined"
                            :full-width="demoFullWidth"
                            :loading="demoState === 'loading'"
                            :disabled="demoState === 'disabled'"
                            label="Botão de Demo"
                            @click="showToast"
                        />
                    </div>
                </div>
            </section>
        </div>

        <!-- Footer -->
        <Footer @link-click="handleFooterLinkClick" />

        <!-- Toast Notifications -->
        <ToastContainer />
    </div>
</template>

<script setup lang="ts">
import Footer from '@/components/layout/Footer.vue';
import Button from '@/components/ui/Button.vue';
import ToastContainer from '@/components/ui/ToastContainer.vue';
import { useToast } from '@/composables/useToastSystem';
import { ref } from 'vue';

const demoVariant = ref<'primary' | 'secondary' | 'glass' | 'outline' | 'ghost' | 'destructive' | 'success'>('primary');
const demoSize = ref<'xs' | 'sm' | 'md' | 'lg' | 'xl'>('md');
const demoIcon = ref('star');
const demoState = ref('normal');
const demoFullWidth = ref(false);

const { success, error, warning, info, persistent, clearAllToasts } = useToast();

const showToast = () => {
    success('Botão clicado!', 'Demo do sistema de botões funcionando perfeitamente 🎬');
};

const showMultipleToasts = () => {
    success('Sucesso!', 'Operação realizada com sucesso');
    setTimeout(() => {
        info('Informação', 'Processando dados...', 3000);
    }, 500);
    setTimeout(() => {
        warning('Atenção', 'Alguns itens precisam de revisão', 4000);
    }, 1000);
    setTimeout(() => {
        error('Erro', 'Falha na conexão com o servidor', 6000);
    }, 1500);
};

const showPersistentToast = () => {
    persistent('info', 'Notificação Persistente', 'Esta notificação não desaparece automaticamente');
};

const clearAll = () => {
    clearAllToasts();
};

const handleFooterLinkClick = (section: string) => {
    info('Navegação', `Você clicou em: ${section}`);
};
</script>
