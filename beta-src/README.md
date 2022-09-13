## Build for production

We are using Soketi (Pusher) to support WebSockets. To build the react app with the respective ENV variables, we need to
create a .env.production file. To do so, you can do cp .env.development .env.production and replace the respective ENV variables. Then you can run:

```
npm run build:production
```
