# SUPLA CLOUD - Frontend

This directory contains sources of frontend components used in SUPLA CLOUD.

They are written with [Vue.js](https://vuejs.org/).
The sources are built with [webpack](https://webpack.github.io/).

## How to build the sources?

Install [Node.js](https://nodejs.org/).

```bash
npm install
npm run build
```

The first command downloads the Internet. The second one builds the sources
to the output directory (`web/assets/dist`).

## How to develop the frontend components?

In order to start the webpack dev server with continous building and hot reloads, 
add the following in your `app/config/parameters.yml`:

```
use_webpack_dev_server: true
```

and run:

```bash
npm run dev
```
