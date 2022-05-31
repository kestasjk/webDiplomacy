import Texture from "../../enums/Texture";

export interface TextureData {
  texture: Texture;
  stroke?: string;
  strokeWidth?: number;
  strokeOpacity?: number;
}
