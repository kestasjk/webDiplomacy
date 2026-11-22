/**
 * Keeps the board alive when the browser translates the page. Google Translate (most of it Chrome on Android)
 * replaces text nodes with its own <font> elements, so when React later removes or inserts beside a text node it
 * rendered, that node is no longer where React left it, the DOM throws NotFoundError, and the board falls into its
 * error boundary. This is the usual workaround (facebook/react#11538): skip a removal of a node that has already
 * moved, and append when the reference node has moved. The translated text can then go stale until the page is
 * reloaded, which is better than losing the board, and the page stays translatable.
 */
export default function installTranslationGuard(): void {
  if (typeof Node !== "function" || !Node.prototype) return;

  const originalRemoveChild = Node.prototype.removeChild;
  Node.prototype.removeChild = function removeChild<T extends Node>(
    this: Node,
    child: T,
  ): T {
    if (child.parentNode !== this) return child;
    return originalRemoveChild.call(this, child) as T;
  };

  const originalInsertBefore = Node.prototype.insertBefore;
  Node.prototype.insertBefore = function insertBefore<T extends Node>(
    this: Node,
    newNode: T,
    referenceNode: Node | null,
  ): T {
    if (referenceNode && referenceNode.parentNode !== this) {
      return originalInsertBefore.call(this, newNode, null) as T;
    }
    return originalInsertBefore.call(this, newNode, referenceNode) as T;
  };
}
