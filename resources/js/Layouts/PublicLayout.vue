<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const props = defineProps({
    canLogin: {
        type: Boolean,
        default: true
    }
});

const showLoginSelector = ref(false);
const showRegisterSelector = ref(false);

const handleLoginToggle = () => {
    showLoginSelector.value = !showLoginSelector.value;
};

const handleRegisterToggle = () => {
    showRegisterSelector.value = !showRegisterSelector.value;
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-gray-900 font-sans selection:bg-indigo-100 dark:selection:bg-indigo-900/40">
        <!-- Navigation -->
        <nav class="fixed top-0 w-full z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border-b border-gray-100 dark:border-gray-800">
            <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                <Link href="/">
                    <!-- Logo compacto para que no sobresalga de la barra -->
                    <ApplicationLogo compact />
                </Link>
                
                <div class="hidden md:flex items-center gap-8">
                    <a href="/#inicio" class="text-sm font-bold text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">Inicio</a>
                    <a href="/#especialidades" class="text-sm font-bold text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">Especialidades</a>
                    <a href="/#doctores" class="text-sm font-bold text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 transition-colors">Doctores</a>
                </div>

                <div v-if="canLogin" class="flex items-center gap-4">
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="text-sm font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest">Mi Panel</Link>
                    <template v-else>
                        <button @click="handleLoginToggle" class="text-sm font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest hover:text-indigo-600 transition-colors">Ingresar</button>
                        <button @click="handleRegisterToggle" class="px-6 py-3 bg-indigo-600 text-white rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 dark:shadow-none">Registrarse</button>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="pt-20">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-900 pt-20 pb-10 border-t border-gray-100 dark:border-gray-800 px-6">
            <div class="max-w-7xl mx-auto">
                <div class="grid md:grid-cols-4 gap-12 mb-20">
                    <div class="col-span-1 md:col-span-2">
                        <!-- Logo en footer puede usar la versión completa -->
                        <ApplicationLogo class="mb-8" />
                        <p class="text-gray-500 dark:text-gray-400 font-medium max-w-sm leading-relaxed">
                            Turno Médico es la plataforma líder en gestión de citas médicas digitales, conectando a miles de profesionales con pacientes que buscan atención de calidad.
                        </p>
                    </div>
                    
                    <div>
                        <h5 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-8">Enlaces Rápidos</h5>
                        <ul class="space-y-4">
                            <li><a href="/#inicio" class="text-sm font-bold text-gray-500 hover:text-indigo-600 transition-colors">Buscador</a></li>
                            <li><a href="/#doctores" class="text-sm font-bold text-gray-500 hover:text-indigo-600 transition-colors">Doctores</a></li>
                            <li><a href="/#especialidades" class="text-sm font-bold text-gray-500 hover:text-indigo-600 transition-colors">Especialidades</a></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h5 class="text-xs font-black uppercase tracking-widest text-gray-400 mb-8">Contacto</h5>
                        <ul class="space-y-4">
                            <li class="text-sm font-bold text-gray-500">contacto@turnomedico.com</li>
                            <li class="text-sm font-bold text-gray-500">+1 (800) MED-DOCS</li>
                        </ul>
                    </div>
                </div>
                
                <div class="pt-10 border-t border-gray-100 dark:border-gray-800 flex flex-col md:flex-row justify-between items-center gap-6">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">© 2026 Turno Médico. Todos los derechos reservados.</p>
                    <div class="flex gap-6">
                        <a href="#" class="text-gray-400 hover:text-indigo-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-indigo-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Role Selection Modal (Login) -->
        <div v-if="showLoginSelector" class="fixed inset-0 z-[100] flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" @click="handleLoginToggle"></div>
            
            <div class="relative bg-white dark:bg-gray-800 w-full max-w-2xl rounded-[3rem] shadow-2xl border border-gray-100 dark:border-gray-700 p-8 md:p-12 animate-in zoom-in duration-300">
                <button @click="handleLoginToggle" class="absolute top-8 right-8 text-gray-400 hover:text-gray-600 dark:hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>

                <div class="text-center mb-12">
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white mb-4 uppercase tracking-tight">Bienvenido de Nuevo</h3>
                    <p class="text-gray-500 dark:text-gray-400 font-medium tracking-wide">Selecciona cómo deseas ingresar a Turno Médico</p>
                </div>

                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Patient Path -->
                    <Link :href="route('login', { role: 'patient' })" class="group relative bg-indigo-50 dark:bg-indigo-900/30 p-8 rounded-[2rem] text-center hover:bg-indigo-600 transition-all duration-500 shadow-sm hover:shadow-2xl hover:shadow-indigo-200">
                        <div class="w-20 h-20 bg-white dark:bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-6 text-indigo-600 group-hover:scale-110 transition-transform shadow-sm">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <h4 class="text-xl font-black text-indigo-600 group-hover:text-white mb-2 uppercase tracking-tight">Soy Paciente</h4>
                        <p class="text-indigo-400 group-hover:text-indigo-100 text-[10px] font-black uppercase tracking-widest">Mis citas y salud</p>
                        <div class="absolute inset-0 border-2 border-transparent group-hover:border-white/20 rounded-[2rem]"></div>
                    </Link>

                    <!-- Doctor Path -->
                    <Link :href="route('login', { role: 'doctor' })" class="group relative bg-emerald-50 dark:bg-emerald-900/30 p-8 rounded-[2rem] text-center hover:bg-emerald-600 transition-all duration-500 shadow-sm hover:shadow-2xl hover:shadow-emerald-200">
                        <div class="w-20 h-20 bg-white dark:bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-6 text-emerald-600 group-hover:scale-110 transition-transform shadow-sm">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.691.34a6 6 0 01-3.86.517l-2.387-.477a2 2 0 00-1.022.547l-1.162 1.163a1 1 0 001.414 1.414l1.163-1.163 2.387.477a4 4 0 002.573-.344l.69-.34a4 4 0 002.574-.344l2.387.477 1.162 1.163a1 1 0 001.414-1.414l-1.162-1.163z"></path></svg>
                        </div>
                        <h4 class="text-xl font-black text-emerald-600 group-hover:text-white mb-2 uppercase tracking-tight">Soy Médico</h4>
                        <p class="text-emerald-400 group-hover:text-emerald-100 text-[10px] font-black uppercase tracking-widest">Mi consultorio digital</p>
                        <div class="absolute inset-0 border-2 border-transparent group-hover:border-white/20 rounded-[2rem]"></div>
                    </Link>
                </div>

                <p class="text-center mt-12 text-gray-400 text-[10px] font-black uppercase tracking-widest">
                    Administrador? <Link :href="route('login')" class="text-indigo-600 hover:text-indigo-400 transition ml-2">Haz clic aquí</Link>
                </p>
            </div>
        </div>

        <!-- Role Selection Modal (Registration) -->
        <div v-if="showRegisterSelector" class="fixed inset-0 z-[100] flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" @click="handleRegisterToggle"></div>
            
            <div class="relative bg-white dark:bg-gray-800 w-full max-w-2xl rounded-[3rem] shadow-2xl border border-gray-100 dark:border-gray-700 p-8 md:p-12 animate-in zoom-in duration-300">
                <button @click="handleRegisterToggle" class="absolute top-8 right-8 text-gray-400 hover:text-gray-600 dark:hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>

                <div class="text-center mb-12">
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white mb-4 uppercase tracking-tight">Únete a Nosotros</h3>
                    <p class="text-gray-500 dark:text-gray-400 font-medium tracking-wide">Crea tu cuenta profesional o de paciente en segundos</p>
                </div>

                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Patient Path -->
                    <Link :href="route('register', { role: 'patient' })" class="group relative bg-indigo-50 dark:bg-indigo-900/30 p-8 rounded-[2rem] text-center hover:bg-indigo-600 transition-all duration-500 shadow-sm hover:shadow-2xl hover:shadow-indigo-200">
                        <div class="w-20 h-20 bg-white dark:bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-6 text-indigo-600 group-hover:scale-110 transition-transform shadow-sm">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <h4 class="text-xl font-black text-indigo-600 group-hover:text-white mb-2 uppercase tracking-tight">Soy Paciente</h4>
                        <p class="text-indigo-400 group-hover:text-indigo-100 text-[10px] font-black uppercase tracking-widest">Agendar mis citas</p>
                        <div class="absolute inset-0 border-2 border-transparent group-hover:border-white/20 rounded-[2rem]"></div>
                    </Link>

                    <!-- Doctor Path -->
                    <Link :href="route('register', { role: 'doctor' })" class="group relative bg-emerald-50 dark:bg-emerald-900/30 p-8 rounded-[2rem] text-center hover:bg-emerald-600 transition-all duration-500 shadow-sm hover:shadow-2xl hover:shadow-emerald-200">
                        <div class="w-20 h-20 bg-white dark:bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-6 text-emerald-600 group-hover:scale-110 transition-transform shadow-sm">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.691.34a6 6 0 01-3.86.517l-2.387-.477a2 2 0 00-1.022.547l-1.162 1.163a1 1 0 001.414 1.414l1.163-1.163 2.387.477a4 4 0 002.573-.344l.69-.34a4 4 0 002.574-.344l2.387.477 1.162 1.163a1 1 0 001.414-1.414l-1.162-1.163z"></path></svg>
                        </div>
                        <h4 class="text-xl font-black text-emerald-600 group-hover:text-white mb-2 uppercase tracking-tight">Soy Médico</h4>
                        <p class="text-emerald-400 group-hover:text-emerald-100 text-[10px] font-black uppercase tracking-widest">Gestionar mi agenda</p>
                        <div class="absolute inset-0 border-2 border-transparent group-hover:border-white/20 rounded-[2rem]"></div>
                    </Link>
                </div>

                <p class="text-center mt-12 text-gray-400 text-[10px] font-black uppercase tracking-widest">
                    ¿Ya tienes cuenta? <button @click="() => { handleRegisterToggle(); handleLoginToggle(); }" class="text-indigo-600 hover:text-indigo-400 transition ml-2">Ingresa aquí</button>
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-in {
    animation-duration: 0.3s;
    animation-fill-mode: both;
}
.zoom-in {
    animation-name: zoomIn;
}
@keyframes zoomIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
