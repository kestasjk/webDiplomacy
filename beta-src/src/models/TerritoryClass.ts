import BoardClass from "./BoardClass";
import ConvoyGroupClass from "./ConvoyGroupClass";
import UnitClass from "./UnitClass";

import PathSearchClass from "./PathSearchClass";
import PathClass from "./PathClass";

import { CoastType, TerritoryType } from "./enums";
import {
  ITerritory,
  IBorder,
  ICoastalBorder,
  IProvinceStatus,
} from "./Interfaces";

export default class TerritoryClass {
  id!: string;

  countryID!: string;

  coast!: keyof typeof CoastType;

  coastParent!: TerritoryClass;

  coastParentID!: string;

  coastChildren!: Set<TerritoryClass>;

  convoyLink!: boolean;

  name!: string;

  occupiedFromTerrID!: string;

  ownerCountryID!: string;

  supply!: boolean;

  standoff!: boolean;

  type!: keyof typeof TerritoryType;

  unitID?: string;

  Borders!: IBorder[];

  CoastalBorders!: ICoastalBorder[];

  ConvoyGroup!: ConvoyGroupClass;

  ConvoyGroups!: ConvoyGroupClass[];

  Unit!: UnitClass;

  convoyNode = true;

  board: BoardClass;

  // contains the last search that went
  // through this node -> needed for breadth first search
  lastVisitedBy: PathSearchClass | null = null;

  // contains the last search that went through this node and was not success.
  // In case a new search passes this node with a same or higher rank, no
  // additional search has to be done  (as there won't be a success in this
  // case as well)
  // (will only be updated, when lastVisitedBy is updated or state is called
  // by internal functions!)
  lastSearchNotSuccess: PathSearchClass | null = null;

  // the current path that uses this node
  path: PathClass | null = null;

  // a cache for the border territories that are important for the path search
  validBorderTerritoriesCache: TerritoryClass[] | any[] = [];

  // data of the current search (the overall one; for validBorderTerritories)
  search: any;

  // A mark only used for checking, if two paths are seperated or not
  inPath = null;

  constructor(
    terrData: ITerritory,
    board: BoardClass,
    terrStatusData?: IProvinceStatus,
  ) {
    Object.assign(this, {
      ...terrData,
      supply: terrData.supply === "Yes",
      ConvoyGroups: [],
      convoyLink: false,
      coastChildren: new Set<TerritoryClass>(),
    });

    if (terrStatusData) {
      Object.assign(this, terrStatusData);
    }

    this.board = board;
  }

  setUnit(unit: UnitClass) {
    this.Unit = unit;
  }

  setCoastParent(coastParent: TerritoryClass) {
    const { Borders, supply } = coastParent;

    this.coastParent = coastParent;
    this.Borders = Borders;
    this.supply = supply;
  }

  addCoastChild(coastChild: TerritoryClass) {
    this.coastChildren.add(coastChild);
  }

  setConvoyLink() {
    this.convoyLink = true;
  }

  setConvoyGroups(convoyGroup: ConvoyGroupClass) {
    this.ConvoyGroups.push(convoyGroup);
  }

  setConvoyGroup(convoyGroup: ConvoyGroupClass) {
    this.ConvoyGroup = convoyGroup;
  }

  nodeInit(search) {
    this.convoyNode = true;

    // contains the last search that went
    // through this node -> needed for breadth first search
    this.lastVisitedBy = null;

    // contains the last search that went through this node and was not success.
    // In case a new search passes this node with a same or higher rank, no
    // additional search has to be done  (as there won't be a success in this
    // case as well)
    // (will only be updated, when lastVisitedBy is updated or state is called
    // by internal functions!)
    this.lastSearchNotSuccess = null;

    // the current path that uses this node
    this.path = null;

    // a cache for the border territories that are important for the path search
    this.validBorderTerritoriesCache = [];

    // data of the current search (the overall one; for validBorderTerritories)
    this.search = search;

    // A mark only used for checking, if two paths are seperated or not
    this.inPath = null;
  }

  visited(node) {
    return this.lastVisitedBy === node;
  }

  setVisited(search) {
    // update lastSuccessfulSearch so no information gets lost
    // if (!this.lastVisitedBy === null && !this.lastVisitedBy.success) {
    //   this.lastSearchNotSuccess = this.lastVisitedBy;
    // }

    if (this.lastVisitedBy && !this.lastVisitedBy.success) {
      this.lastSearchNotSuccess = this.lastVisitedBy;
    }

    this.lastVisitedBy = search;
  }

  /*
   * Returns a number (rank) to decide if this node might lead to a
   * successfull search.
   *
   * In case this node is part of a complete path, the rank (number of
   * alternative routes) of the path at this node is returned. Only if
   * the rank is lower than that of the current search's starting territory
   * this node can be part of the new path.
   *
   * In case this node is not part of a complete path, the rank of the
   * last unsuccessful search (i.e. the rank of the starting node) is
   * reaturned. Only if the rank of the current search is lower, it might
   * lead to a successful search.
   */
  getMaxRank() {
    if (this.hasPath() && this.path) {
      return this.path.getRank();
    }

    return this.lastSearchNotSuccess !== null
      ? this.lastSearchNotSuccess.path.getNextNodeRank()
      : Infinity;
  }

  hasPath() {
    return !(this.path === null);
  }

  isConvoyNode() {
    return !!this.convoyNode && this.convoyNode;
  }

  // add function to cache valid border territories with specific search params for efficiency
  getValidBorderTerritories() {
    if (!this.validBorderTerritoriesCache.length)
      this.validBorderTerritoriesCache = this.Borders.filter((b) => {
        return b.f;
      })
        .map((b) => {
          return this.board.findTerritoryByID(b.id);
        })
        .filter((n) => {
          if (!this.search) {
            return false;
          }
          if (n) {
            return (
              n.isConvoyNode() &&
              n.id !== this.search.startTerr.id &&
              (this.search.fAllNode(n) || this.search.fEndNode(n))
            );
          }

          return false;
        });

    return this.validBorderTerritoriesCache;
  }
}
