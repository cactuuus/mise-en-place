import {createApp} from 'vue'
import {createPinia} from "pinia";
import {Notify, Quasar} from 'quasar'
import router from './router'
import App from './App.vue'
import 'quasar/dist/quasar.css'
import '@quasar/extras/material-icons/material-icons.css'

const app = createApp(App)
const pinia = createPinia()

// Register plugins
app.use(pinia)
app.use(router)
app.use(Quasar, {
    plugins: {
        Notify
    }
})

app.mount('#app')
