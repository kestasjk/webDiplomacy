import PathSearchClass from "./PathSearchClass";
import TerritoryClass from "./TerritoryClass";

export default class PathClass {
  complete = false;

  alternativeChecked = false;

  pathNextNodes: TerritoryClass[] | any[] = [];

  rank = 0;

  // eslint-disable-next-line no-useless-constructor
  constructor(public node, public pathToNode) {}

  includes(node) {
    if (this.node === node) {
      return true;
    }

    if (this.pathToNode == null) {
      return false;
    }

    return this.pathToNode.includes(node);
  }

  /*
   * Creates a new path identical to this one apart from the fact, that a new
   * node was added at the end.
   */
  addNode(node) {
    return new PathClass(node, this);
  }

  /*
   * removes the first element of the path and return the new first one
   */
  removeFirst() {
    if (this.pathToNode == null) {
      return;
    }

    if (this.pathToNode.pathToNode != null) {
      this.pathToNode.removeFirst();
    }

    this.newPathToNode(null);
  }

  /*
   * The path is a complete path from start node to end node. Set it is
   * fixed in each node for further searches.
   */
  setComplete(nextNode) {
    this.node.path = this;

    // assure, the rank is set to 0 if this node is the start
    if (this.pathToNode === null) {
      this.setRank(0);
    }

    if (!!nextNode && this.pathNextNodes.indexOf(nextNode) === -1) {
      // adjust the ranks / count of alternative routes
      this.pathNextNodes.forEach((pnn) => {
        pnn.changeRank(1);
      });

      nextNode.setRank(this.pathNextNodes.length);

      this.pathNextNodes.push(nextNode);
    }

    // previous nodes of complete path are already set to complete
    if (!this.complete) {
      this.complete = true;

      if (this.pathToNode !== null) {
        this.pathToNode.setComplete(this);
      }
    }
  }

  /*
   * To avoid resource intensive rank updates for all following nodes
   * everytime the rank is changed, the (real) rank is only calculated on call
   * by just summing up this rank and all the rank of the predecessors.
   */
  getRank() {
    return (
      this.rank + (this.pathToNode !== null ? this.pathToNode.getRank() : 0)
    );
  }

  /*
   * Changes the rank for this path node and all following (in case of a complete path).
   *
   * Positive change -> increase
   * Negative change -> decrease
   */
  changeRank(change) {
    this.rank += change;
  }

  /*
   * Sets this rank and adjust the rank of following nodes
   */
  setRank(newRank) {
    const diff = newRank - this.rank;

    this.changeRank(diff);
  }

  /*
   * get the last node of the path (normally end node)
   */
  getLastPathNode() {
    if (this.pathNextNodes.length === 0) {
      // this is the last element of the path
      return this;
    }

    // (arbitrarily) choose the first of the next nodes instead
    return this.pathNextNodes[0].getLastPathNode();
  }

  removePathNextNode(path) {
    if (!this.complete) {
      return;
    }

    const index = this.pathNextNodes.indexOf(path);

    if (index === -1) {
      return;
    }

    this.pathNextNodes.splice(this.pathNextNodes.indexOf(path), 1);

    this.pathNextNodes.forEach((pnn) => {
      pnn.changeRank(-1);
    });
    // this.pathNextNodes.invoke("changeRank", -1); // decrease rank of all other forks (one alternative was just deleted

    if (this.pathNextNodes.length === 0) {
      // path not part of an alternative path -> dissolve completely
      this.node.path = null;
      this.dissolvePathToNode();
    }
  }

  dissolvePathToNode() {
    if (this.pathToNode != null) {
      this.pathToNode.removePathNextNode(this);
    }

    this.complete = false;
  }

  /*
   * Dissolves the previous path to this node and add new path to node
   */
  newPathToNode(node) {
    this.dissolvePathToNode();

    this.pathToNode = node;

    return this;
  }

  /*
   * Attaches the current path to an existing path through the node.
   *
   * Returns the path node where the reconnection happened.
   */
  attachToNodePath() {
    if (this.node.hasPath()) {
      return this.node.path.newPathToNode(this.pathToNode);
    }
    return this;
  }

  getNextNodeRank() {
    return this.getRank() + this.pathNextNodes.length - 1;
  }

  /*
   * check for this path, if alternative paths starting from this.node
   * to end terr exist.
   */
  searchAlternativeRoutes(fEndNode) {
    if (!this.complete) return;

    /*
     * Only search for alternative routes in relevant cases:
     * - not already done
     * - not end node
     * - not next to end node (no alternative can be found, where this path is not part of)
     */
    if (!this.alternativeChecked) {
      if (
        fEndNode(this.node) ||
        this.pathNextNodes.filter((pnn) => {
          return pnn.node === fEndNode;
        })
      ) {
        this.alternativeChecked = true;
      } else {
        const nextNodeRank = this.getNextNodeRank();
        const alternativeRouteSearch = new PathSearchClass(
          this.node, // start at this node
          (node) => {
            // final node is found or node with higher rank
            return (
              fEndNode(node) ||
              (node.hasPath() && nextNodeRank < node.getMaxRank())
            );
          },
          (node) => {
            /* additional condition an alternative route nodes have to fullfill
             * for optimization:
             *
             * node not already used in search of same rank or lower before
             *		(=> if it is, there are no chances of finding an alternative route)
             */
            return nextNodeRank < node.getMaxRank();
          },
        );

        if (alternativeRouteSearch.findPath()) {
          // there exists an alternativeRoute
          let alternativeRoute = alternativeRouteSearch.path;

          // first check if alternativeRoute reaches endNode or path with lower rank
          if (!fEndNode(alternativeRoute.node)) {
            // not EndNode -> reconnect paths
            alternativeRoute = alternativeRoute.attachToNodePath();
          }

          // append alternativeRoute to current path
          alternativeRoute.removeFirst().newPathToNode(this);

          // next set the route complete
          alternativeRoute.setComplete();

          // now search alternativeRoute for alternativeRoutes (beginning at the node next to end node)
          alternativeRoute.pathToNode.searchAlternativeRoutes(fEndNode);
          /*
           * Note, that not extra check is needed for pathToNode:
           * Even if the alternativeRoute heads directly into endNode,
           * pathToNode is at least this. So the extreme case is that other
           * paths from this node are checked (which is wanted)
           */
        } else {
          // all alternative routes from this node are found
          this.alternativeChecked = true;
        }
      }
    }

    if (this.pathToNode !== null)
      this.pathToNode.searchAlternativeRoutes(fEndNode);
  }

  /*
   * Returns an alternative path that does not pass through this.node.
   * Alternative routes have to be searched before-
   */
  getAlternativeRoute() {
    if (!this.alternativeChecked) {
      return null;
    }

    if (this.pathToNode === null) {
      return null;
    }

    if (this.pathToNode.pathNextNodes.length > 1) {
      // there is a fork in the path at node before
      // find a path to next node of previous node that is not this.
      // and return the complete path
      return this.pathToNode.pathNextNodes
        .find((path) => {
          return path !== this;
        }, this)
        .getLastPathNode();
    }
    return this.pathToNode.getAlternativeRoute();
  }

  /*
   * Checks if this path can be appended to path so a simple path is preserved.
   * So this basically checks if both paths share any common nodes apart from
   * path.lastNode and this.firstNode.
   *
   * Implemented recursivly
   */
  canBeAppendedTo(path) {
    path.markPath(path);

    let retValue = true;

    if (this.node.inPath === path) {
      retValue = false;
    } else if (this.pathToNode !== null) {
      retValue = this.pathToNode.canBeAppendedTo(path);
    }

    return retValue;
  }

  markPath(path) {
    if (this.node.inPath === path) {
      return;
    }

    this.node.inPath = path;

    if (this.pathToNode !== null) {
      this.pathToNode.markPath(path);
    }
  }

  toArray(array: string[] = []) {
    // do not include last element of path in array representation
    array.push(this.node.id);

    if (this.pathToNode != null) {
      return this.pathToNode.toArray(array);
    }

    return array;
  }

  getLength() {
    return 1 + (this.pathToNode !== null ? this.pathToNode.getLength() : 0);
  }

  // DEBUGGING ONLY
  getFirst() {
    if (this.pathToNode === null) {
      return this;
    }

    return this.pathToNode.getFirst();
  }

  getAllPathsFromThis() {
    if (this.pathNextNodes.length === 0) {
      return this;
    }
    return this.pathNextNodes
      .map((pnn) => {
        return pnn.getAllPathsFromThis();
      })
      .flat();
  }
}
