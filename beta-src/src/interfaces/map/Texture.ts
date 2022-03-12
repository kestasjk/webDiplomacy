import TextureEnum from "../../enums/Texture";

export interface Texture {
  texture: TextureEnum;
  stroke?: string;
  strokeWidth: number;
  strokeOpacity: number;
}
