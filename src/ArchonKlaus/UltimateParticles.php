<?php

/*
 * This plugin has been continued by Klaus - ArchonTeam
 * Copyright 2018 ArchonKlaus, www.italianrealms.xyz
 */
namespace ArchonKlaus;

use pocketmine\plugin\PluginDescription;
use pocketmine\plugin\PluginLoader;
use pocketmine\Server;
use ArchonKlaus\task\SpiralTask;
use ArchonKlaus\commands\UltimateParticlesCommand;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\command\CommandSender;

class UltimateParticles extends PluginBase
{

    public function __construct(PluginLoader $loader, Server $server, PluginDescription $description, string $dataFolder, $file)
    {
        parent::__construct($loader, $server, $description, $dataFolder, $file);
    }

    public $data;

    /**
     *
     * @var static $this |null
     */
    private static $instance = null;

    /**
     *
     * @var string
     */
    private $provider = "YAML";

    /**
     *
     * @return UltimateParticles
     */
    public static function getInstance(): UltimateParticles
    {
        return self::$instance;
    }

    public function onEnable()
    {
        $this->getLogger()->info("Loading UltimateParticles!");
        if (!is_dir($this->getDataFolder())) {
            mkdir($this->getDataFolder());
        }
        $this->data = new Config($this->getDataFolder() . "ejector.yml", Config::YAML, array());
        self::$instance = $this;
        $this->particles = new Particles ($this);
        $this->getScheduler()->scheduleRepeatingTask(new SpiralTask ($this), 3);
        $this->getCommand("ultimateparticles")->setExecutor(new UltimateParticlesCommand($this));
        $this->getLogger()->info("§aLoaded Successfully!");
    }

    // For external use
    /*
     * @deprecated
     */
    public function getData($file = "data")
    {
        return $this->data;
    }

    /**
     *
     * @return Particles
     */
    public function getParticles()
    {
        $particles = new Particles ($this);
        return $particles;
    }

    /**
     * @api
     *
     * @param string $name Ejector's name to be an identification of it
     * @param Position $pos Ejector position
     * @param array $particles All kinds of particles to be shown (in array form)
     * @param float $amplifier Frequency of particles' appearance
     * @param string $type "normal" / "spiral"
     *
     * @return boolean
     */
    public function setEjector($name, Position $pos, array $particles, $amplifier = 1, $type = "normal")
    {
        $ed = $this->data->getAll();
        if (array_key_exists($name, $ed) !== false) {
            unset($ed[$name]);
        }
        $ed[$name]["pos"]["x"] = $pos->x;
        $ed[$name]["pos"]["y"] = $pos->y + 1;
        $ed[$name]["pos"]["z"] = $pos->z;
        $ed[$name]["pos"]["world"] = $pos->getLevel()->getName();
        foreach ($particles as $particle) {
            $ed[$name]["particle"][] = $particle;
        }
        $ed[$name]["amplifier"] = $amplifier;
        //TODO: $ed[$name]["type"] = $type;
        $this->data->setAll($ed);
        $this->data->save();
        return true;
    }

    /**
     * @api
     *
     * @param string $name
     *
     * @return boolean
     */
    public function isEjectorExists($name)
    {
        $ed = $this->data->getAll();
        return (bool)array_key_exists($name, $ed);
    }

    /**
     * @api
     *
     * @param string $name
     *
     * @return boolean
     */
    public function removeEjector($name)
    {
        $ed = $this->data->getAll();
        if (array_key_exists($name, $ed)) {
            unset($ed[$name]);
            $this->data->setAll($ed);
            $this->data->save();
            return true;
        }
        return false;
    }

    /**
     * @api
     *
     * @return string
     */
    public function getAllEjectors()
    {
        $ed = $this->data->getAll();
        return implode(", ", array_keys($ed));
    }
}

?>
