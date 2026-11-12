enum ApiRoute {
  // Everything this board reads comes through utils/api/gameSource.ts; these are only the routes it writes with
  // post
  SEND_MESSAGE = "game/sendmessage",
  MESSAGES_SEEN = "game/messagesseen",
  GAME_SETVOTE = "game/setvote",
  SET_BACK_FROM_LEFT = "game/markbackfromleft",
  JOIN_GAME = "game/join",
  LEAVE_GAME = "game/leave",
  // get sandbox
  SANDBOX_COPY = "sandbox/copy",
  SANDBOX_MOVETURNBACK = "sandbox/moveTurnBack",
  SANDBOX_DELETE = "sandbox/delete",
}

export default ApiRoute;
