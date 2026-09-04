import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import Alpine from 'alpinejs'
import { MotionPlugin } from '@vueuse/motion'
import './assets/css/main.css'

const app = createApp(App)

// Register Alpine.js
window.Alpine = Alpine
Alpine.start()

// Register Motion plugin
app.use(MotionPlugin)

// Register router
app.use(router)

app.mount('#app')