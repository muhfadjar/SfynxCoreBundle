<?php
namespace Sfynx\CoreBundle\Layers\Domain\Workflow\Observer\Response;

use Exception;
use Symfony\Component\HttpFoundation\Response;
use Sfynx\CoreBundle\Layers\Domain\Specification\SpecIsCreatedWithRows;
use Sfynx\CoreBundle\Layers\Domain\Workflow\Observer\Generalisation\Response\AbstractCreateResponseJson;
use Sfynx\CoreBundle\Layers\Domain\Service\Response\Serializer\SerializerStrategy;
use Sfynx\CoreBundle\Layers\Domain\Service\Response\Handler\ResponseHandler;
use Sfynx\CoreBundle\Layers\Infrastructure\Exception\ResponseException;

/**
 * Class OBCreateResponseJson
 *
 * @category Sfynx\CoreBundle\Layers
 * @package Domain
 * @subpackage Workflow\Observer\Response
 * @author     Etienne de Longeaux <etienne.delongeaux@gmail.com>
 * @copyright  2016 PI-GROUPE
 */
class OBCreateResponseJson extends AbstractCreateResponseJson
{
    /**
     * The process function ...
     *
     * @return bool False to notify that postprocessing could not be executed.
     * @throws ResponseException
     */
    public function process(): bool
    {
        // we abort if we are not in the create form process
        $specs = new SpecIsCreatedWithRows();
        if (!$specs->isSatisfiedBy($this->object)) {
            $this->wfLastData->rows = [];
        }

        try {
            $url = !\property_exists($this->wfHandler, 'url') ? null : $this->wfHandler->url;
            $this->wfLastData->response = (new ResponseHandler(SerializerStrategy::create(), $this->request->setRequestFormat('json')))
                ->create($this->wfLastData->rows, Response::HTTP_OK, $this->headers, $url)
                ->getResponse();
        } catch (Exception $e) {
            throw ResponseException::noCreatedResponse();
        }
        return true;
    }
}
