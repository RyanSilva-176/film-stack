<template>
    <Dialog :open="isOpen" @update:open="$emit('update:open', $event)">
        <DialogContent class="border-sidebar-border bg-sidebar text-sidebar-foreground sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="text-lg font-semibold text-sidebar-primary">
                    {{ isEditing ? 'Editar Lista' : 'Criar Nova Lista' }}
                </DialogTitle>
                <DialogDescription class="text-sm text-sidebar-accent-foreground">
                    {{ isEditing ? 'Atualize as informações da sua lista.' : 'Crie uma lista personalizada para organizar seus filmes.' }}
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <!-- Nome da Lista -->
                <div class="space-y-2">
                    <Label for="list-name" class="text-sm font-medium text-sidebar-foreground">Nome da Lista *</Label>
                    <Input
                        id="list-name"
                        v-model="form.name"
                        type="text"
                        placeholder="Ex: Filmes para Assistir no Fim de Semana"
                        required
                        :disabled="loading"
                        class="w-full border-sidebar-border bg-sidebar-accent text-sidebar-foreground placeholder:text-sidebar-accent-foreground"
                    />
                    <p v-if="errors.name" class="text-sm text-red-500">{{ errors.name }}</p>
                </div>

                <!-- Descrição -->
                <div class="space-y-2">
                    <Label for="list-description" class="text-sm font-medium text-sidebar-foreground">Descrição</Label>
                    <textarea
                        id="list-description"
                        v-model="form.description"
                        placeholder="Adicione uma descrição opcional para sua lista..."
                        rows="3"
                        :disabled="loading"
                        class="flex min-h-[60px] w-full resize-none rounded-md border border-sidebar-border bg-sidebar-accent px-3 py-2 text-sm text-sidebar-foreground shadow-sm placeholder:text-sidebar-accent-foreground focus-visible:ring-1 focus-visible:ring-sidebar-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    />
                </div>

                <!-- Visibilidade -->
                <div class="space-y-3">
                    <Label class="text-sm font-medium text-sidebar-foreground">Visibilidade</Label>
                    <div class="space-y-2">
                        <div class="flex items-center space-x-2">
                            <input
                                id="private"
                                v-model="form.is_public"
                                :value="false"
                                type="radio"
                                name="visibility"
                                class="h-4 w-4 border-sidebar-border text-sidebar-primary focus:ring-sidebar-ring"
                                :disabled="loading"
                            />
                            <Label for="private" class="cursor-pointer text-sm">
                                <div class="flex items-center gap-2">
                                    <font-awesome-icon :icon="['fas', 'lock']" class="h-4 w-4 text-sidebar-accent-foreground" />
                                    <span class="text-sidebar-foreground">Privada - Apenas você pode ver</span>
                                </div>
                            </Label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <input
                                id="public"
                                v-model="form.is_public"
                                :value="true"
                                type="radio"
                                name="visibility"
                                class="h-4 w-4 border-sidebar-border text-sidebar-primary focus:ring-sidebar-ring"
                                :disabled="loading"
                            />
                            <Label for="public" class="cursor-pointer text-sm">
                                <div class="flex items-center gap-2">
                                    <font-awesome-icon :icon="['fas', 'globe']" class="h-4 w-4 text-sidebar-accent-foreground" />
                                    <span class="text-sidebar-foreground">Pública - Outros usuários podem ver</span>
                                </div>
                            </Label>
                        </div>
                    </div>
                </div>

                <!-- Ações -->
                <div class="flex flex-col-reverse gap-2 pt-4 sm:flex-row">
                    <Button
                        type="button"
                        variant="outline"
                        @click="$emit('update:open', false)"
                        :disabled="loading"
                        class="w-full border-sidebar-border text-sidebar-foreground hover:bg-sidebar-accent sm:w-auto"
                    >
                        Cancelar
                    </Button>
                    <Button
                        type="submit"
                        :disabled="loading || !form.name.trim()"
                        class="w-full bg-sidebar-primary text-sidebar-primary-foreground hover:bg-sidebar-primary/90 sm:w-auto"
                    >
                        <Loader2 v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
                        {{ isEditing ? 'Atualizar Lista' : 'Criar Lista' }}
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import Button from '@/components/ui/Button.vue';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import { useToast } from '@/composables/useToastSystem';
import { useUserListsStore, type MovieList } from '@/stores/userLists';
import { library } from '@fortawesome/fontawesome-svg-core';
import { faGlobe, faLock } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { Loader2 } from 'lucide-vue-next';
import { reactive, ref, watch } from 'vue';

library.add(faLock, faGlobe);

interface Props {
    isOpen: boolean;
    list?: MovieList | null;
}

interface Emits {
    (e: 'update:open', value: boolean): void;
    (e: 'success', list: MovieList): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const userListsStore = useUserListsStore();
const { success, error: showError } = useToast();

const loading = ref(false);
const isEditing = ref(false);

const form = reactive({
    name: '',
    description: '',
    is_public: false,
});

const errors = reactive({
    name: '',
});

const resetForm = () => {
    form.name = '';
    form.description = '';
    form.is_public = false;
    errors.name = '';
};

watch(
    () => props.list,
    (newList) => {
        if (newList) {
            isEditing.value = true;
            form.name = newList.name;
            form.description = newList.description || '';
            form.is_public = newList.is_public;
        } else {
            isEditing.value = false;
            resetForm();
        }
    },
    { immediate: true },
);

watch(
    () => props.isOpen,
    (isOpen) => {
        if (!isOpen && !isEditing.value) {
            resetForm();
        }
    },
);

const validateForm = () => {
    errors.name = '';

    if (!form.name.trim()) {
        errors.name = 'O nome da lista é obrigatório.';
        return false;
    }

    if (form.name.trim().length < 3) {
        errors.name = 'O nome deve ter pelo menos 3 caracteres.';
        return false;
    }

    if (form.name.trim().length > 100) {
        errors.name = 'O nome não pode ter mais de 100 caracteres.';
        return false;
    }

    return true;
};

const handleSubmit = async () => {
    if (!validateForm()) return;

    loading.value = true;

    try {
        if (isEditing.value && props.list) {
            showError('Funcionalidade em Desenvolvimento', 'A edição de listas será implementada em breve.');
        } else {
            const result = await userListsStore.createCustomList(form.name.trim(), form.description.trim() || undefined, form.is_public);

            if (result.success) {
                success('Lista Criada!', `A lista "${form.name}" foi criada com sucesso.`);

                emit('success', result.data);
                emit('update:open', false);
                resetForm();
            }
        }
    } catch (error) {
        console.error('Error handling list:', error);
        showError('Erro!', isEditing.value ? 'Não foi possível atualizar a lista.' : 'Não foi possível criar a lista.');
    } finally {
        loading.value = false;
    }
};
</script>
