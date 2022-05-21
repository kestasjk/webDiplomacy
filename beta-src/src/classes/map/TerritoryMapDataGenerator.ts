import {
  BBox,
  Coordinates,
  Label,
  TerritoryI,
  TerritoryMapData,
  Texture,
  UnitSlot,
} from "../../interfaces";
import webDiplomacyTheme from "../../webDiplomacyTheme";
import Country from "../../enums/Country";

export interface TerritoryMapDataGeneratorDrawData extends BBox {
  arrowReceiver?: Coordinates;
  centerPos?: Coordinates;
  country?: Country;
  fill?: string;
  labels?: Label[];
  path: string;
  playable: boolean;
  texture?: Texture;
  unitSlots?: UnitSlot[];
  viewBox?: string;
}

interface TerritoryMapDataGeneratorInterface
  extends TerritoryI,
    TerritoryMapDataGeneratorDrawData {}

export default class TerritoryMapDataGenerator implements TerritoryMapData {
  public territory: TerritoryMapData["territory"];

  public abbr: TerritoryMapData["abbr"];

  public arrowReceiver: TerritoryMapData["arrowReceiver"];

  public centerPos: TerritoryMapData["centerPos"];

  public fill: TerritoryMapData["fill"];

  public height: TerritoryMapData["height"];

  public labels: TerritoryMapData["labels"];

  public path: TerritoryMapData["path"];

  public type: TerritoryMapData["type"];

  public unitSlots: TerritoryMapData["unitSlots"];

  public unitSlotsBySlotName: TerritoryMapData["unitSlotsBySlotName"];

  public width: TerritoryMapData["width"];

  public x: TerritoryMapData["x"];

  public y: TerritoryMapData["y"];

  public playable: TerritoryMapData["playable"];

  public texture: TerritoryMapData["texture"];

  public viewBox: TerritoryMapData["viewBox"];

  constructor({
    territory,
    abbr,
    arrowReceiver = undefined,
    centerPos = undefined,
    country = undefined,
    fill = "none",
    height,
    labels,
    path,
    type,
    unitSlots,
    width,
    x,
    y,
    playable,
    texture = undefined,
  }: TerritoryMapDataGeneratorInterface) {
    this.abbr = abbr;
    this.arrowReceiver = arrowReceiver;
    this.centerPos = centerPos;
    this.fill = fill;
    this.height = height;
    this.labels = labels;
    this.path = path;
    this.type = type;
    this.playable = playable;
    this.unitSlots = unitSlots;
    this.unitSlotsBySlotName = {};
    if (this.unitSlots) {
      this.unitSlots.forEach((unitSlot) => {
        this.unitSlotsBySlotName[unitSlot.name] = unitSlot;
      });
    }

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
      territory: this.territory;
      abbr: this.abbr,
      arrowReceiver: this.arrowReceiver,
      centerPos: this.centerPos,
      fill: this.fill,
      height: this.height,
      labels: this.labels,
      path: this.path,
      type: this.type,
      unitSlots: this.unitSlots,
      unitSlotsBySlotName: this.unitSlotsBySlotName,
      width: this.width,
      x: this.x,
      y: this.y,
      playable: this.playable,
      texture: this.texture,
      viewBox: this.viewBox,
    };
  }
}
