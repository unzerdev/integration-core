<?php

namespace Unzer\Core\BusinessLogic\CheckoutAPI\CommonFlow\Controller;

use Unzer\Core\BusinessLogic\CheckoutAPI\CommonFlow\Request\CommonFlowRequest;
use Unzer\Core\BusinessLogic\CheckoutAPI\CommonFlow\Response\CommonFlowResponse;

interface CommonFlowControllerInterface
{
    /**
     * @param CommonFlowRequest $request
     *
     * @return CommonFlowResponse
     */
    public function create(CommonFlowRequest $request): CommonFlowResponse;
}
