<?php

class PluginTest extends TestCase
{
    public function test_plugin_installed() {
        activate_plugin( 'zume-simulator/zume-simulator.php' );

        $this->assertContains(
            'zume-simulator/zume-simulator.php',
            get_option( 'active_plugins' )
        );
    }
}
