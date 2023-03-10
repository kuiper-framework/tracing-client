<?php

/*
 * This file is part of the Kuiper package.
 *
 * (c) Ye Wenbin <wenbinye@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace kuiper\tracing;

use OpenTracing\Scope as OTScope;
use OpenTracing\Span as OTSpan;

/**
 * {@inheritdoc}
 */
class Scope implements OTScope
{
    /**
     * @var ScopeManager
     */
    private $scopeManager;

    /**
     * @var OTSpan
     */
    private $wrapped;

    /**
     * @var OTScope|null
     */
    private $toRestore;

    /**
     * @var bool
     */
    private $finishSpanOnClose;

    /**
     * Scope constructor.
     *
     * @param ScopeManager $scopeManager
     * @param OTSpan       $wrapped
     * @param bool         $finishSpanOnClose
     */
    public function __construct(ScopeManager $scopeManager, OTSpan $wrapped, bool $finishSpanOnClose)
    {
        $this->scopeManager = $scopeManager;
        $this->wrapped = $wrapped;
        $this->finishSpanOnClose = $finishSpanOnClose;
        $this->toRestore = $scopeManager->getActive();
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        if ($this->scopeManager->getActive() !== $this) {
            // This shouldn't happen if users call methods in expected order
            return;
        }

        if ($this->finishSpanOnClose) {
            $this->wrapped->finish();
        }

        $this->scopeManager->setActive($this->toRestore);
    }

    /**
     * {@inheritdoc}
     */
    public function getSpan(): OTSpan
    {
        return $this->wrapped;
    }
}
