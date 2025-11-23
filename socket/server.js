const express = require("express");
const http = require("http");
const { Server } = require("socket.io");
const cors = require("cors");

const app = express();
app.use(cors());

const server = http.createServer(app);

const io = new Server(server, {
  cors: {
    origin: "*", // luego podrás restringir
  },
});

// Conexión principal
io.on("connection", (socket) => {
  console.log("Cliente conectado:", socket.id);

  // Cuando un usuario entra a un chat específico
  socket.on("joinChat", (chatId) => {
    socket.join("chat_" + chatId);
  });

  // Cuando alguien envía un mensaje
  socket.on("nuevoMensaje", (data) => {
    io.to("chat_" + data.chat_id).emit("mensajeRecibido", data);
  });

  socket.on("disconnect", () => {
    console.log("Cliente desconectado:", socket.id);
  });
});

server.listen(3001, () => {
  console.log("Socket.IO en http://localhost:3001");
});
