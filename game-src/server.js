/* eslint-disable import/newline-after-import */
/* eslint-disable @typescript-eslint/no-var-requires */
/* eslint-disable import/no-extraneous-dependencies */

// This is a basic express server to serve the compiled game folder.
// It could be used in production to do server rendering or you can try in your local environment
// To run it:
// 1. build the app if you didn't do it before
// 2. npm run start:local
// 3. go to your browser and open http://localhost:8896

const path = require("path");
const express = require("express");
const app = express(); // create express app

// add middleware
app.use("/game", express.static(path.join(__dirname, "..", "game")));

app.use((req, res, next) => {
  res.sendFile(path.join(__dirname, "..", "game", "index.html"));
});

// start express server on port 8896
app.listen(8896, () => {
  console.info("Server started on port 8896");
});
