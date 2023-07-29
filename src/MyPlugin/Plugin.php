<?php

class MyPlugin_Plugin
{
    /**
     * The plugin event manager.
     *
     * @var MyPlugin_EventManagement_EventManager
     */
    private $event_manager;

    /**
     * The plugin router.
     *
     * @var MyPlugin_Routing_Router
     */
    private $router;

    /**
     * Flag to track if the plugin is loaded.
     *
     * @var bool
     */
    private $loaded;

    /**
     * The basename of the plugin.
     *
     * @var string
     */
    private $basename;

    /**
     * Constructor.
     */
    public function __construct($file)
    {
        $this->basename = plugin_basename($file);
        $this->event_manager = new MyPlugin_EventManagement_EventManager();
        $this->loaded = false;
        $this->router = new MyPlugin_Routing_Router();
    }

     /**
     * Loads the plugin into WordPress.
     */
    public function load()
    {
        if ($this->loaded) {
            return;
        }

        foreach ($this->get_routes() as $route) {
            $this->router->add_route($route);
        }

        foreach ($this->get_subscribers() as $subscriber) {
            $this->event_manager->add_subscriber($subscriber);
        }

        $this->loaded = true;
    }

     /**
     * Get the plugin routes.
     *
     * @return MyPlugin_Routing_Route[]
     */
    private function get_routes()
    {
        return $this->event_manager->filter('myplugin_routes', 
        array(
            // Our plugin routes
        ));
    }

    /**
     * Get the plugin event subscribers.
     *
     * @return MyPlugin_EventManagement_SubscriberInterface[]
     */
    private function get_subscribers()
    {
        return $this->event_manager->filter('myplugin_subscribers', 
        array(
            new MyPlugin_Subscriber_CustomPostTypeSubscriber(),
        ));
    }
}