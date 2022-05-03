import PathClass from "./PathClass";

export default class PathSearchClass {
  success = false;

  path: any = null;

  // eslint-disable-next-line no-useless-constructor
  constructor(public startTerr, public fEndNode, public fAllNode?) {}

  /*
   * Find a path from start node to endNode with simple breadth-first search
   *
   * Note that fEndNode and fAllNode might not be the ones
   * of the general overall search, but search specific. Especially fAllNode
   * contains path specific restrictions.
   *
   * Returns true, if a path was found
   */
  findPath(forceInternalNode = false) {
    // at least one internal node can be enforced in case of direct
    // path searches from land to land that must include at least
    // one fleet.

    // start with initial path only containing StartTerr
    const start = new PathClass(this.startTerr, null);
    start.node.setVisited(this);

    const testPaths = start.node
      .getValidBorderTerritories()
      .filter((nextNode) => {
        // skip the end node as one starting node if an internal node should be enforced
        return !forceInternalNode || !this.fEndNode(nextNode);
      }, this)
      .map((nextNode) => {
        return start.addNode(nextNode);
      });

    while (testPaths.length > 0) {
      const testPath = testPaths.shift();

      // check if path is found
      if (this.fEndNode(testPath.node)) {
        this.success = true;
        this.path = testPath;
        return true;
      }

      // check if node was already visited or fails fAllNode conditions
      if (
        !testPath.visited(this) &&
        this.fAllNode &&
        !this.fAllNode(testPath)
      ) {
        // set the node visited
        testPath.setVisited(this);

        // create new branches of the path, that reach to neighbored valid territories
        const NextNodes = testPath.getValidBorderTerritories();

        // add new paths to testPaths
        NextNodes.each((nextNode) => {
          testPaths.push(testPath.addNode(nextNode));
        });
      }
    }

    // no path is found
    return false;
  }
}
