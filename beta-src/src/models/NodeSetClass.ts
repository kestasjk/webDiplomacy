import PathSearchClass from "./PathSearchClass";
import TerritoryClass from "./TerritoryClass";

export default class NodeSetClass {
  Nodes: Record<string, TerritoryClass> = {};

  Path: any;

  addNodes(Nodes: TerritoryClass[]) {
    Nodes.map((n) => {
      return this.addNode(n);
    });
  }

  addNode(Node: TerritoryClass) {
    this.Nodes[Node.id] = Node;
  }

  initNodes(search) {
    Object.values(this.Nodes).map((n) => {
      return n.nodeInit(search);
    });
  }

  routeSetLoad(ConvoyGroup) {
    [...ConvoyGroup.fleets].map((f) => {
      return this.addNode(f.Territory);
    });

    [...ConvoyGroup.armies].map((a) => {
      return this.addNode(a.Territory);
    });

    [...ConvoyGroup.coasts].map((c) => {
      return this.addNode(c);
    });
  }

  routeSetStart(StartTerr, fEndNode, fAllNode, fAnyNode) {
    // initialize nodes and load valied border territories for this search
    this.initNodes(new PathSearchClass(StartTerr, fEndNode, fAllNode));

    const AnyNodes: TerritoryClass[] = Object.values(this.Nodes).filter(
      fAnyNode,
    );

    if (AnyNodes.length === 1) {
      let EndTerr = Object.values(this.Nodes).find(fEndNode);

      // The EndTerr might not be part of the Convoy group.
      // In this case no valid path can be found.
      if (!EndTerr) return false;

      // find path simple path to AnyNode (from now on middle node)
      const fMiddleNode = function (node) {
        return node.id === AnyNodes[0].id;
      };

      /*
       * Calculate two paths from start to middle and end to middle (which is works fast).
       *
       * If those two paths are seperated ones, the problem is already
       * solved and we can take a short cut.
       *
       * If not, take the shorter one to calculate alternatives
       * (as shorter paths might have lesser alternatives from a
       * statistical point of view).
       *
       */

      // first path
      let search = new PathSearchClass(StartTerr, fMiddleNode);

      if (!search.findPath()) {
        return false;
      }

      // second path
      const search2 = new PathSearchClass(EndTerr, fMiddleNode);

      if (!search2.findPath()) {
        return false;
      }

      // are the two found paths seperated (apart from middleNode)
      if (!search2.path.pathToNode.canBeAppendedTo(search.path)) {
        // if paths are not already found

        // check which path is shorter to search for alternatives
        if (search.path.getLength() > search2.path.getLength()) {
          search = search2;
          EndTerr = StartTerr; // we now started our search at end terr, so start terr is the new end terr
        }

        // set this path fixed as complete path for further search
        search.path.setComplete();

        // find all alternative routes to end node
        // (beginning at the node next to end node)
        search.path.pathToNode.searchAlternativeRoutes(fMiddleNode);

        // now see if there exists a second path from end node to middle node
        const newSearch2 = new PathSearchClass(
          EndTerr, // start search at end node
          (node) => {
            // end nodes are the middle node or a path node with a complete fork before (rank > 0)
            // (which can be used as alternative route from start to middle)
            return (
              fMiddleNode(node) || (node.hasPath() && node.getMaxRank() > 0)
            );
          },
          (node) => {
            // ignore nodes with paths in general (those should only be end nodes if rank > 0)
            return !node.hasPath();
          },
        );

        if (!newSearch2.findPath()) return false;
      }

      /*
       * there exists a path start - middle - end
       * But it has to be completed first:
       * - a fitting path start - middle has to be found
       * - path2 might be completed from a node where it connected to one
       * of the first paths
       */
      let path1; // the final chosen first part of the whole path
      let path2; // the final chosen second part of the whole path

      // first check if second search reaches middleNode (or just a part of an alternative route)
      if (fMiddleNode(search2.path.node)) {
        // it does directly reach endNode
        // the initial path can be chosen as path1
        path1 = search.path;
        path2 = search2.path;
      } else {
        // not EndNode -> path2 connects to one path from first paths
        // -> find alternative first path and then reconnect path2
        path1 = search2.path.node.path.getAlternativeRoute();

        path2 = search2.path.attachToNodePath().getLastPathNode();
      }

      // now there exist to disjoint paths, one from start and one from end to middle
      // -> build the final path
      // toArray generates an element starting with the last element
      // -> path1 has to be reversed
      // middleNode should not be included twice
      // -> chose path2.pathToNode instead
      this.Path = path1.toArray().reverse().concat(path2.pathToNode.toArray());

      // the path might still be reversed at this point if a search
      // backwards was considered more efficient

      if (this.Path[0] !== StartTerr.id) {
        this.Path.reverse();
      }

      // do not include endNode in the final -> remove last element
      this.Path.pop();

      return true;
    }

    if (AnyNodes.length === Object.keys(this.Nodes).length) {
      const search = new PathSearchClass(StartTerr, fEndNode);
      if (!search.findPath(true)) {
        return false;
      }

      this.Path = search.path.toArray().reverse();
      this.Path.pop();

      return true;
    }

    return false;
  }
}
