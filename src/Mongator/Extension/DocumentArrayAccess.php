<?php

/*
 * This file is part of Mongator.
 *
 * (c) Pablo Díez <pablodip@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Mongator\Extension;

use Mandango\Mondator\Extension;

/**
 * DocumentArrayAccess extension.
 *
 * @author Pablo Díez <pablodip@gmail.com>
 */
class DocumentArrayAccess extends Extension
{
    /**
     * {@inheritdoc}
     */
    protected function doClassProcess()
    {
        $this->definitions['document_base']->addInterface('\ArrayAccess');

        $this->processTemplate($this->definitions['document_base'], file_get_contents(__DIR__.'/templates/DocumentArrayAccess.php.twig'));
    }
}
