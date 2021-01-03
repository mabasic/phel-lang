<?php

declare(strict_types=1);

namespace Phel\Compiler\Parser\Reader\ExpressionReader;

use Phel\Compiler\Parser\Parser\ParserNode\MetaNode;
use Phel\Compiler\Parser\Reader;
use Phel\Exceptions\ReaderException;
use Phel\Lang\AbstractType;
use Phel\Lang\Keyword;
use Phel\Lang\MetaInterface;
use Phel\Lang\Symbol;
use Phel\Lang\Table;

final class MetaReader
{
    private Reader $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @return AbstractType|string|float|int|bool
     */
    public function read(MetaNode $node)
    {
        $metaExpression = $node->getMetaNode();
        $objectExpression = $node->getObjectNode();

        $meta = $this->reader->readExpression($metaExpression);
        if (is_string($meta) || $meta instanceof Symbol) {
            $meta = Table::fromKVs(new Keyword('tag'), $meta);
        } elseif ($meta instanceof Keyword) {
            $meta = Table::fromKVs($meta, true);
        } elseif (!$meta instanceof Table) {
            throw ReaderException::forNode($node, 'Metadata must be a Symbol, String, Keyword or Table');
        }
        $object = $this->reader->readExpression($objectExpression);

        if (!$object instanceof MetaInterface) {
            throw ReaderException::forNode($node, 'Metadata can only applied to classes that implement MetaInterface');
        }

        $objMeta = $object->getMeta();
        foreach ($meta as $k => $v) {
            if ($k) {
                $objMeta[$k] = $v;
            }
        }
        $object->setMeta($objMeta);

        return $object;
    }
}
