import _ from 'lodash';
import axios from 'axios';
// import Echo from 'laravel-echo';
// import Pusher from 'pusher-js';


// Load lodash
window._ = _;


// Load axios and set default headers
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


// Echo setup (uncomment if needed)
// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });



