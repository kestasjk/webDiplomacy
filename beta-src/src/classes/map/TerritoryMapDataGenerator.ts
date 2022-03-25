import { TerritoryMapData } from "../../interfaces";
import webDiplomacyTheme from "../../webDiplomacyTheme";
import Country from "../../enums/Country";

interface TerritoryMapDataGeneratorInterface extends TerritoryMapData {
  country?: Country;
}

export default class TerritoryMapDataGenerator
  implements TerritoryMapDataGeneratorInterface
{
  public abbr: TerritoryMapData["abbr"];

  public centerPos: TerritoryMapData["centerPos"];

  public fill: TerritoryMapData["fill"];

  public height: TerritoryMapData["height"];

  public labels: TerritoryMapData["labels"];

  public name: TerritoryMapData["name"];

  public path: TerritoryMapData["path"];

  public type: TerritoryMapData["type"];

  public unitSlots: TerritoryMapData["unitSlots"];

  public width: TerritoryMapData["width"];

  public x: TerritoryMapData["x"];

  public y: TerritoryMapData["y"];

  public texture: TerritoryMapData["texture"];

  public viewBox: TerritoryMapData["viewBox"];

  constructor({
    abbr,
    centerPos = undefined,
    country = undefined,
    fill = "none",
    height,
    labels,
    name,
    path,
    type,
    unitSlots,
    width,
    x,
    y,
    texture = undefined,
  }: TerritoryMapDataGeneratorInterface) {
    this.abbr = abbr;
    this.centerPos = centerPos;
    this.fill = fill;
    this.height = height;
    this.labels = labels;
    this.name = name;
    this.path = path;
    this.type = type;
    this.unitSlots = unitSlots;
    this.width = width;
    this.x = x;
    this.y = y;
    let textureConfig: TerritoryMapData["texture"];
    if (texture) {
      let textureStroke;
      if (country) {
        textureStroke = webDiplomacyTheme.palette[country].main;
      }
      textureConfig = {
        texture: texture.texture,
        stroke: texture.stroke || textureStroke,
        strokeOpacity: texture.strokeOpacity || 0.5,
        strokeWidth: texture.strokeWidth || 7,
      };
    }
    this.texture = textureConfig;
    this.viewBox = `0 0 ${width} ${height}`;
  }

  get territory(): TerritoryMapData {
    return {
      abbr: this.abbr,
      centerPos: this.centerPos,
      fill: this.fill,
      height: this.height,
      labels: this.labels,
      name: this.name,
      path: this.path,
      type: this.type,
      unitSlots: this.unitSlots,
      width: this.width,
      x: this.x,
      y: this.y,
      texture: this.texture,
      viewBox: this.viewBox,
    };
  }
}
