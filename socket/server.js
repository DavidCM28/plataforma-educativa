const express = require("express");
const http = require("http");
const { Server } = require("socket.io");
const cors = require("cors");

const app = express();
app.use(cors());

const server = http.createServer(app);

// SOCKET CONFIG
const io = new Server(server, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"],
  },
});

// Usuarios conectados
let usuariosConectados = new Map();

// ==========================
//  CONEXIÓN PRINCIPAL
// ==========================
io.on("connection", (socket) => {
  console.log("🔵 Cliente conectado:", socket.id);

  // ---------------------------
  // Registrar usuario
  // ---------------------------
  socket.on("registrarUsuario", (userId) => {
    if (!usuariosConectados.has(userId)) {
      usuariosConectados.set(userId, []);
    }

    usuariosConectados.get(userId).push(socket.id);
    socket.userId = userId;

    console.log(`🟢 Usuario ${userId} en socket ${socket.id}`);

    // Notificar presencia inmediata
    io.emit("usuarioOnline", userId);

    // Enviar lista inicial solo a este cliente
    socket.emit("listaOnline", [...usuariosConectados.keys()]);
  });

  // ---------------------------
  // Unirse a chat
  // ---------------------------
  socket.on("joinChat", (chatId) => {
    socket.join("chat_" + chatId);
  });

  // ---------------------------
  // Typing...
  // ---------------------------
  socket.on("typing", ({ chatId, userId }) => {
    socket.to("chat_" + chatId).emit("usuarioTyping", userId);
  });

  socket.on("stopTyping", ({ chatId, userId }) => {
    socket.to("chat_" + chatId).emit("usuarioStopTyping", userId);
  });

  // ---------------------------
  // Mensaje nuevo
  // ---------------------------
  socket.on("nuevoMensaje", (data) => {
    const chatRoom = "chat_" + data.chat_id;

    console.log("📥 SERVER NUEVO MENSAJE =>", data);

    // Enviar a los que están dentro del chat
    io.to(chatRoom).emit("mensajeRecibido", data);

    // Reenviar a sockets del receptor
    const participantes = data.participantes || [];

    participantes.forEach((p) => {
      const uid = typeof p === "object" ? p.usuario_id : p;

      // 👉 NO reenviar si el receptor YA está dentro del chat
      if (io.sockets.adapter.rooms.get(chatRoom)?.size > 1) {
        return;
      }

      const sockets = usuariosConectados.get(uid) || [];

      sockets.forEach((socketId) => {
        if (uid != data.emisor_id) {
          io.to(socketId).emit("mensajeRecibido", data);
        }
      });
    });
  });

  // ---------------------------
  // Archivos adjuntos
  // ---------------------------
  socket.on("nuevoArchivo", (data) => {
    io.to("chat_" + data.chat_id).emit("archivoRecibido", data);
  });

  // ---------------------------
  // Mensajes marcados como leídos
  // ---------------------------
  socket.on("marcarLeido", (data) => {
    const { chatId, lector_id } = data;

    console.log(`👁 Mensajes de chat ${chatId} fueron leídos por ${lector_id}`);

    io.to("chat_" + chatId).emit("mensajesLeidos", {
      chatId,
      lector_id,
    });
  });

  // ---------------------------
  // Desconexión
  // ---------------------------
  socket.on("disconnect", () => {
    const userId = socket.userId;
    if (!userId) return;

    const sockets = usuariosConectados.get(userId) || [];
    const filtrados = sockets.filter((id) => id !== socket.id);

    if (filtrados.length > 0) {
      usuariosConectados.set(userId, filtrados);
    } else {
      usuariosConectados.delete(userId);
      io.emit("usuarioOffline", userId); // Avisar
    }

    console.log(`🔴 Usuario ${userId} desconectó socket ${socket.id}`);
  });
});

// ==========================
//  ENDPOINT: usuarios online
// ==========================
app.get("/online", (req, res) => {
  res.json([...usuariosConectados.keys()]);
});

server.listen(3001, () => {
  console.log("Socket.IO corriendo en http://localhost:3001");
});
