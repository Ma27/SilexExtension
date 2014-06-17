<?php
namespace Ma27\SilexExtension;

/**
 * List of keys in the container
 * 
 * @author Maximilian Bosch <ma27-se@hotmail.com>
 * @copyright (c) 2014 - 2018, Maximilian Bosch
 */
final class Parameters
{
    /**
     * Key of the list containing all 
     * controller reponse filters
     * 
     * @var string
     * 
     * @api
     */
    const HANDLER_STACK     = 'app.handlers';
    
    /**
     * Key of the current controller action id 
     * 
     * @var string
     * 
     * @api
     */
    const CURRENT_ACTION_ID = 'app.controller.current_action';
    
    /**
     * Request attribute key of the 
     * initial template to render in the 
     * request handling
     * 
     * @var string
     * 
     * @api
     */
    const TEMPLATE_KEY      = 'result.params.template';
    
    /**
     * Alias of the template engine to render 
     * templates of the controllers in the container
     * 
     * @var string
     * 
     * @api
     */
    const TEMPLATE_ENGINE   = 'app.templating';
}