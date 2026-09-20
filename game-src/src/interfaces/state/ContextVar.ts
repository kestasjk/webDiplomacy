import { IContext } from "../../models/Interfaces";

interface ContextVar {
  context: IContext;
  contextKey: string;
  // The context as the server signed it, when it came from game/playercontext; this is what has to be posted
  // back with the key. A context returned by an order save only comes as an object.
  contextString?: string;
}

export default ContextVar;
