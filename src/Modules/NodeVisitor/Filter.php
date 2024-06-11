<?php // phpcs:ignore WordPress.Files.FileName
/**
 * The Filter class.
 *
 * Code in this class has been ported from
 * https://github.com/snicco/php-scoper-excludes/
 *
 * All credit goes to the original author.
 * https://github.com/snicco/
 */

namespace Sematico\Scoper\Modules\NodeVisitor;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal
 */
final class Filter extends NodeVisitorAbstract {
	public function enterNode( Node $node ) {
		if ($node instanceof Node\Stmt\Namespace_) {
			return null;
		}

		// We don't need to traverse child nodes like methods on classes since we
		// are only interested in the root names.
		// This way we improve performance by a lot.
		if ($this->isOfInterest( $node )) {
			return NodeTraverser::DONT_TRAVERSE_CHILDREN;
		}
	}

	public function leaveNode( Node $node ): ?int {
		if ( ! $this->isOfInterest( $node ) && $node instanceof Node\Stmt) {
			return NodeTraverser::REMOVE_NODE;
		}
		return null;
	}

	private function isOfInterest( Node $node ): bool {
		return $node instanceof Node\Stmt\Class_
				|| $node instanceof Node\Stmt\Function_
				|| $node instanceof Node\Stmt\Trait_
				|| $node instanceof Node\Stmt\Const_
				|| $node instanceof Node\Stmt\Interface_
				|| ( $node instanceof Node\Stmt\Expression
					&& $node->expr instanceof Node\Expr\FuncCall
					&& $node->expr->name instanceof Node\Name
					&& $node->expr->name->toString() === 'define'
				);
	}
}
