import {createApp} from 'vue'
import {Quasar} from 'quasar'
import router from './router'
import App from './App.vue'

import 'quasar/dist/quasar.css'
import '@quasar/extras/fontawesome-v6/fontawesome-v6.css'

const app = createApp(App)

// Register plugins
app.use(router)
app.use(Quasar, {
    plugins: {}
})

app.mount('#app')
