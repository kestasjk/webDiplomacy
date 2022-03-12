import { TerritoryMapData } from "../../interfaces";
import TerritoryEnum from "../../enums/Territory";
import webDiplomacyTheme from "../../webDiplomacyTheme";
import Country from "../../enums/Country";

interface TerritoryMapDataGeneratorInterface extends TerritoryMapData {
  country?: Country;
}

export default class TerritoryMapDataGenerator
  implements TerritoryMapDataGeneratorInterface
{
  public name: TerritoryEnum;

  public abbr: TerritoryMapData["abbr"];

  public type: TerritoryMapData["type"];

  public centerPos: TerritoryMapData["centerPos"];

  public labels: TerritoryMapData["labels"];

  public unitSlot: TerritoryMapData["unitSlot"];

  public height: TerritoryMapData["height"];

  public width: TerritoryMapData["width"];

  public x: TerritoryMapData["x"];

  public y: TerritoryMapData["y"];

  public path: TerritoryMapData["path"];

  public fill: TerritoryMapData["fill"];

  public texture: TerritoryMapData["texture"];

  public viewBox: TerritoryMapData["viewBox"];

  constructor({
    name,
    abbr,
    type,
    centerPos = undefined,
    labels,
    unitSlot,
    height,
    width,
    x,
    y,
    path,
    fill = "none",
    texture = undefined,
    country = undefined,
  }: TerritoryMapDataGeneratorInterface) {
    this.name = name;
    this.abbr = abbr;
    this.type = type;
    this.centerPos = centerPos;
    this.labels = labels;
    this.unitSlot = unitSlot;
    this.x = x;
    this.y = y;
    this.height = height;
    this.width = width;
    this.path = path;
    this.fill = fill;
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

  get(): TerritoryMapData {
    return {
      name: this.name,
      abbr: this.abbr,
      type: this.type,
      centerPos: this.centerPos,
      labels: this.labels,
      unitSlot: this.unitSlot,
      width: this.width,
      height: this.height,
      x: this.x,
      y: this.y,
      path: this.path,
      fill: this.fill,
      texture: this.texture,
      viewBox: this.viewBox,
    };
  }
}
