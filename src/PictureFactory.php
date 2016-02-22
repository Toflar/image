<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Image;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;

/**
 * Creates Picture objects.
 *
 * @author Martin Auswöger <martin@auswoeger.com>
 */
class PictureFactory
{
    /**
     * @var PictureGenerator
     */
    private $pictureGenerator;

    /**
     * @var ImageFactory
     */
    private $imageFactory;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * Constructor.
     *
     * @param PictureGenerator         $pictureGenerator The picture generator
     * @param ImageFactory             $imageFactory     The image factory
     * @param ContaoFrameworkInterface $framework        The Contao framework
     */
    public function __construct(
        PictureGenerator $pictureGenerator,
        ImageFactory $imageFactory,
        ContaoFrameworkInterface $framework
    ) {
        $this->pictureGenerator = $pictureGenerator;
        $this->imageFactory = $imageFactory;
        $this->framework = $framework;
    }

    /**
     * Creates a Picture object.
     *
     * @param string    $path The path to the source image
     * @param int|array $size The ID of an image size or an array with width
     *                        height and resize mode
     *
     * @return Picture The created Picture object
     */
    public function create($path, $size)
    {
        $image = $this->imageFactory->create($path);

        $config = $this->createConfig($size);

        return $this->pictureGenerator->generate($image, $config);
    }

    private function createConfig($size)
    {
        if (!is_array($size)) {
            $size = [0, 0, $size];
        }

        $config = new PictureConfiguration();

        if (!isset($size[2]) || !is_numeric($size[2])) {
            $resizeConfig = new ResizeConfiguration();
            if (isset($size[0]) && $size[0]) {
                $resizeConfig->setWidth($size[0]);
            }
            if (isset($size[1]) && $size[1]) {
                $resizeConfig->setHeight($size[1]);
            }
            if (isset($size[2]) && $size[2]) {
                $resizeConfig->setMode($size[2]);
            }
            $configItem = new PictureConfigurationItem();
            $configItem->setResizeConfig($resizeConfig);
            $config->setSize($configItem);

            return $config;
        }

        $config->setSize($this->createConfigItem(
            $this->framework
                ->getAdapter('Contao\\ImageSizeModel')
                ->findByPk($size[2])
        ));

        $imageSizeItems = $this->framework
            ->getAdapter('Contao\\ImageSizeItemModel')
            ->findVisibleByPid($size[2], ['order' => 'sorting ASC']);

        if ($imageSizeItems !== null) {
            $configItems = [];
            foreach ($imageSizeItems as $imageSizeItem) {
                $configItems[] = $this->createConfigItem($imageSizeItem);
            }
            $config->setSizeItems($configItems);
        }

        return $config;
    }

    private function createConfigItem($imageSize)
    {
        $configItem = new PictureConfigurationItem();
        $resizeConfig = new ResizeConfiguration();

        if (null !== $imageSize) {
            $resizeConfig
                ->setWidth($imageSize->width)
                ->setHeight($imageSize->height)
                ->setMode($imageSize->resizeMode)
                ->setZoomLevel($imageSize->zoom);

            $configItem
                ->setResizeConfig($resizeConfig)
                ->setSizes($imageSize->sizes)
                ->setDensities($imageSize->densities);

            if (isset($imageSize->media)) {
                $configItem->setMedia($imageSize->media);
            }
        }

        return $configItem;
    }
}
