<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\Image;

use Imagine\Image\BoxInterface;
use Imagine\Image\PointInterface;
use Imagine\Image\Point;

/**
 * Resize coordinates class.
 *
 * @author Martin Auswöger <martin@auswoeger.com>
 */
class ResizeCoordinates implements ResizeCoordinatesInterface
{
    /**
     * @var BoxInterface
     */
    private $size;

    /**
     * @var PointInterface
     */
    private $cropStart;

    /**
     * @var BoxInterface
     */
    private $cropSize;

    /**
     * Constructor.
     *
     * @param BoxInterface   $size
     * @param PointInterface $cropStart
     * @param BoxInterface   $cropSize
     */
    public function __construct(BoxInterface $size, PointInterface $cropStart, BoxInterface $cropSize)
    {
        $this->size = $size;
        $this->cropStart = $cropStart;
        $this->cropSize = $cropSize;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getCropStart()
    {
        return $this->cropStart;
    }

    /**
     * {@inheritdoc}
     */
    public function getCropSize()
    {
        return $this->cropSize;
    }

    /**
     * {@inheritdoc}
     */
    public function getHash()
    {
        return md5(implode(',', [
            (int) $this->size->getWidth(),
            (int) $this->size->getHeight(),
            (int) $this->cropStart->getX(),
            (int) $this->cropStart->getY(),
            (int) $this->cropSize->getWidth(),
            (int) $this->cropSize->getHeight(),
        ]));
    }

    /**
     * {@inheritdoc}
     */
    public function isEqualTo($coordinates)
    {
        if ($coordinates instanceof BoxInterface) {
            $coordinates = new static($coordinates, new Point(0, 0), $coordinates);
        }

        if (!$coordinates instanceof ResizeCoordinatesInterface) {
            throw new \InvalidArgumentException(sprintf(
                '$coordinates must be an instance of ResizeCoordinatesInterface or BoxInterface, "%s" given',
                get_class($coordinates)
            ));
        }

        /* @var ResizeCoordinatesInterface $coordinates */
        return $this->cropStart->getX() === $coordinates->getCropStart()->getX()
            && $this->cropStart->getY() === $coordinates->getCropStart()->getY()
            && $this->cropSize->getWidth() === $coordinates->getCropSize()->getWidth()
            && $this->cropSize->getHeight() === $coordinates->getCropSize()->getHeight()
            && $this->size->getWidth() === $coordinates->getSize()->getWidth()
            && $this->size->getHeight() === $coordinates->getSize()->getHeight()
        ;
    }
}
