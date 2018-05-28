<?php

namespace Entities;

use D2EM, Log;

use Entities\{
    SwitchPort as SwitchPortEntity
};

use \OSS_SNMP\MIBS\Iface as SNMPIface;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entities\Switcher
 */
class Switcher
{
    const TYPE_SWITCH        = 1;

    /**
     * Elements for SNMP polling via the OSS_SNMP library
     *
     * These are used to build function names
     *
     * @see snmpPoll() below
     * @var array Elements for SNMP polling via the OSS_SNMP library
     */
    public static $SNMP_SWITCH_ELEMENTS = [
        'Model',
        'Os',
        'OsDate',
        'OsVersion',
        'SerialNumber'
    ];

    /**
     * @var string $name
     */
    protected $name;

    /**
     * @var string $ipv4addr
     */
    protected $ipv4addr;

    /**
     * @var string $ipv6addr
     */
    protected $ipv6addr;

    /**
     * @var string $snmppasswd
     */
    protected $snmppasswd;

    /**
     * @var \Entities\Infrastructure
     */
    protected $Infrastructure;

    /**
     * @var integer $switchtype
     */
    protected $switchtype;

    /**
     * @var string $model
     */
    protected $model;

    /**
     * @var string $notes
     */
    protected $notes;

    /**
     * @var integer $asn
     */
    protected $asn;

    /**
     * @var string $loopback_ip
     */
    protected $loopback_ip;

    /**
     * @var string $loopback_name
     */
    protected $loopback_name;

    /**
     * @var string $mgmt_mac_address
     */
    protected $mgmt_mac_address;

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var boolean
     */
    private $mauSupported;

    /**
     * @var string
     */
    private $serialNumber;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $Ports;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $ConsoleServerConnections;

    /**
     * @var \Entities\Cabinet
     */
    protected $Cabinet;

    /**
     * @var \Entities\Vendor
     */
    protected $Vendor;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Ports = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ConsoleServerConnections = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Switcher
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set ipv4addr
     *
     * @param string $ipv4addr
     * @return Switcher
     */
    public function setIpv4addr($ipv4addr)
    {
        $this->ipv4addr = $ipv4addr;

        return $this;
    }

    /**
     * Get ipv4addr
     *
     * @return string
     */
    public function getIpv4addr()
    {
        return $this->ipv4addr;
    }

    /**
     * Set ipv6addr
     *
     * @param string $ipv6addr
     * @return Switcher
     */
    public function setIpv6addr($ipv6addr)
    {
        $this->ipv6addr = $ipv6addr;

        return $this;
    }

    /**
     * Get ipv6addr
     *
     * @return string
     */
    public function getIpv6addr()
    {
        return $this->ipv6addr;
    }

    /**
     * Set snmppasswd
     *
     * @param string $snmppasswd
     * @return Switcher
     */
    public function setSnmppasswd($snmppasswd)
    {
        $this->snmppasswd = $snmppasswd;

        return $this;
    }

    /**
     * Get snmppasswd
     *
     * @return string
     */
    public function getSnmppasswd()
    {
        return $this->snmppasswd;
    }

    /**
     * Set infrastructure
     *
     * @param \Entities\Infrastructure $infrastructure
     * @return Switcher
     */
    public function setInfrastructure($infrastructure)
    {
        $this->Infrastructure = $infrastructure;

        return $this;
    }

    /**
     * Get infrastructure
     *
     * @return \Entities\Infrastructure
     */
    public function getInfrastructure()
    {
        return $this->Infrastructure;
    }

    /**
     * Set switchtype
     *
     * @param integer $switchtype
     * @return Switcher
     */
    public function setSwitchtype($switchtype)
    {
        $this->switchtype = $switchtype;

        return $this;
    }

    /**
     * Get switchtype
     *
     * @return integer
     */
    public function getSwitchtype()
    {
        return $this->switchtype;
    }

    /**
     * Set model
     *
     * @param string $model
     * @return Switcher
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set notes
     *
     * @param string $notes
     * @return Switcher
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add Ports
     *
     * @param \Entities\SwitchPort $ports
     * @return Switcher
     */
    public function addPort(\Entities\SwitchPort $ports)
    {
        $this->Ports[] = $ports;

        return $this;
    }

    /**
     * Remove Ports
     *
     * @param \Entities\SwitchPort $ports
     */
    public function removePort(\Entities\SwitchPort $ports)
    {
        $this->Ports->removeElement($ports);
    }

    /**
     * Get Ports
     *
     * @return \Entities\SwitchPort $ports
     */
    public function getPorts()
    {
        return $this->Ports;
    }

    /**
     * Add ConsoleServerConnections
     *
     * @param \Entities\ConsoleServerConnection $consoleServerConnections
     * @return Switcher
     */
    public function addConsoleServerConnection(\Entities\ConsoleServerConnection $consoleServerConnections)
    {
        $this->ConsoleServerConnections[] = $consoleServerConnections;

        return $this;
    }

    /**
     * Remove ConsoleServerConnections
     *
     * @param \Entities\ConsoleServerConnection $consoleServerConnections
     */
    public function removeConsoleServerConnection(\Entities\ConsoleServerConnection $consoleServerConnections)
    {
        $this->ConsoleServerConnections->removeElement($consoleServerConnections);
    }

    /**
     * Get ConsoleServerConnections
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConsoleServerConnections()
    {
        return $this->ConsoleServerConnections;
    }

    /**
     * Set Cabinet
     *
     * @param \Entities\Cabinet $cabinet
     * @return Switcher
     */
    public function setCabinet(\Entities\Cabinet $cabinet = null)
    {
        $this->Cabinet = $cabinet;

        return $this;
    }

    /**
     * Get Cabinet
     *
     * @return \Entities\Cabinet
     */
    public function getCabinet()
    {
        return $this->Cabinet;
    }

    /**
     * Set Vendor
     *
     * @param \Entities\Vendor $vendor
     * @return Switcher
     */
    public function setVendor(\Entities\Vendor $vendor = null)
    {
        $this->Vendor = $vendor;

        return $this;
    }

    /**
     * Get Vendor
     *
     * @return \Entities\Vendor
     */
    public function getVendor()
    {
        return $this->Vendor;
    }


    /**
     * @var boolean $active
     */
    protected $active;


    /**
     * Set active
     *
     * @param boolean $active
     * @return Switcher
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }
    /**
     * @var string
     */
    protected $hostname;


    /**
     * Set hostname
     *
     * @param string $hostname
     * @return Switcher
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;

        return $this;
    }

    /**
     * Get hostname
     *
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }
    /**
     * @var string
     */
    protected $os;

    /**
     * @var \DateTime
     */
    protected $osDate;

    /**
     * @var string
     */
    protected $osVersion;

    /**
     * @var \DateTime
     */
    protected $lastPolled;


    /**
     * Set os
     *
     * @param string $os
     * @return Switcher
     */
    public function setOs($os)
    {
        $this->os = $os;

        return $this;
    }

    /**
     * Get os
     *
     * @return string
     */
    public function getOs()
    {
        return $this->os;
    }

    /**
     * Set osDate
     *
     * @param \DateTime $osDate
     * @return Switcher
     */
    public function setOsDate($osDate)
    {
        $this->osDate = $osDate;

        return $this;
    }

    /**
     * Get osDate
     *
     * @return \DateTime
     */
    public function getOsDate()
    {
        return $this->osDate;
    }

    /**
     * Set osVersion
     *
     * @param string $osVersion
     * @return Switcher
     */
    public function setOsVersion($osVersion)
    {
        $this->osVersion = $osVersion;

        return $this;
    }

    /**
     * Get osVersion
     *
     * @return string
     */
    public function getOsVersion()
    {
        return $this->osVersion;
    }

    /**
     * Set lastPolled
     *
     * @param \DateTime $lastPolled
     * @return Switcher
     */
    public function setLastPolled($lastPolled)
    {
        $this->lastPolled = $lastPolled;

        return $this;
    }

    /**
     * Get lastPolled
     *
     * @return \DateTime
     */
    public function getLastPolled()
    {
        return $this->lastPolled;
    }



    /**
     * Update switch's details using SNMP polling
     *
     * @see self::$SNMP_SWITCH_ELEMENTS
     *
     * @param \OSS_SNMP\SNMP $host An instance of \OSS_SNMP\SNMP for this switch
     * @param bool $logger An instance of the logger or false
     * @return \Entities\Switcher For fluent interfaces
     */
    public function snmpPoll( $host, bool $logger = false ){
        // utility to format dates
        $formatDate = function( $d ) {
            return $d instanceof \DateTime ? $d->format( 'Y-m-d H:i:s' ) : 'Unknown';
        };

        foreach( self::$SNMP_SWITCH_ELEMENTS as $p ) {
            $fn = "get{$p}";
            $n = $host->getPlatform()->$fn();

            if( $logger ) {
                switch( $p ) {
                    case 'OsDate':
                        if( $formatDate( $this->$fn() ) != $formatDate( $n ) )
                            Log::info( " [{$this->getName()}] Platform: Updating {$p} from " . $formatDate( $this->$fn() ) . " to " . $formatDate( $n ) );
                        else
                            Log::info( " [{$this->getName()}] Platform: Found {$p}: " . $formatDate( $n ) );
                        break;

                    default:
                        if( $logger && $this->$fn() != $n )
                            Log::info( " [{$this->getName()}] Platform: Updating {$p} from {$this->$fn()} to {$n}" );
                        else
                            Log::info( " [{$this->getName()}] Platform: Found {$p}: {$n}" );
                        break;
                }
            }

            $fn = "set{$p}";
            $this->$fn( $n );
        }

        // does this switch support the IANA MAU MIB?
        try {
            $host->useMAU()->types();
            $this->setMauSupported( true );
        } catch( \OSS_SNMP\Exception $e ) {
            $this->setMauSupported( false );
        }

        $this->setLastPolled( new \DateTime() );
        return $this;
    }


    /**
     * Update a switches ports using SNMP polling
     *
     * There is an optional ``$results`` array which can be passed by reference. If
     * so, it will be indexed by the SNMP port index (or a decresing nagative index
     * beginning -1 if the port only exists in the database). The contents of this
     * associative array is:
     *
     *     "port"   => \Entities\SwitchPort object
     *     "bullet" =>
     *         - false for existing ports
     *         - "new" for newly found ports
     *         - "db" for ports that exist in the database only
     *
     * **Note:** It is assumed that the Doctrine2 Entity Manager is available in the
     * Zend registry as ``d2em`` in this function.
     *
     * @param \OSS_SNMP\SNMP $host An instance of \OSS_SNMP\SNMP for this switch
     * @param bool $logger An instance of the logger or false
     * @param bool $result
     *
     * @return \Entities\Switcher For fluent interfaces
     *
     * @throws \OSS_SNMP\Exception
     * @throws \Zend_Exception
     */
    public function snmpPollSwitchPorts( $host, $logger = false, &$result = false ){
        // clone the ports currently known to this switch as we'll be playing with this array
        $existingPorts = clone $this->getPorts();

        // iterate over all the ports discovered on the switch:
        foreach( $host->useIface()->indexes() as $index ) {

            // we're only interested in Ethernet ports here (right?)
            if( $host->useIface()->types()[ $index ] != SNMPIface::IF_TYPE_ETHERNETCSMACD )
                continue;

            // find the matching switchport that may already be in the database (or create a new one)
            $sp = false;

            foreach( $existingPorts as $ix => $ep ) {
                if( $ep->getIfIndex() == $index ) {
                    $sp = $ep;
                    if( is_array( $result ) ){
                        $result[ $index ] = [ "port" => $sp, 'bullet' => false ];
                    }

                    if( $logger ) {
                        Log::info( " - {$this->getName()} - found pre-existing port for ifIndex {$index}" );
                    }

                    // remove this from the array so later we'll know what ports exist only in the database
                    unset( $existingPorts[ $ix ] );
                    break;
                }
            }

            $new = false;
            if( !$sp ) {
                // no existing port in database so we have found a new port
                $sp = new SwitchPortEntity;
                D2EM::persist( $sp );

                $sp->setSwitcher(   $this );
                $sp->setIfIndex(    $index );
                $sp->setActive(     true );
                $sp->setType( SwitchPortEntity::TYPE_UNSET );

                $this->addPort( $sp );

                if( is_array( $result ) ) {
                    $result[ $index ] = [ "port" => $sp, 'bullet' => "new" ];
                }
                $new = true;

                if( $logger ) {
                    Log::info( "Found new port for {$this->getName()} with index $index" );
                }
            }

            // update / set port details from SNMP
            $sp->snmpUpdate( $host, $logger );
        }

        if( count( $existingPorts ) ) {
            $i = -1;
            foreach( $existingPorts as $ep ) {
                if( is_array( $result ) ) {
                    $result[ $i-- ] = [ "port" => $ep, 'bullet' => "db" ];
                }
                if( $logger ) {
                    Log::warning( "{$this->getName()} - port found in database with no matching port on the switch:  [{$ep->getId()}] {$ep->getName()}" );
                }
            }
        }

        return $this;
    }




    /**
     * Set serialNumber
     *
     * @param string $serialNumber
     * @return Switcher
     */
    public function setSerialNumber($serialNumber)
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    /**
     * Get serialNumber
     *
     * @return string
     */
    public function getSerialNumber()
    {
        return $this->serialNumber;
    }



    /**
     * Set mauSupported
     *
     * @param boolean $mauSupported
     * @return Switcher
     */
    public function setMauSupported($mauSupported)
    {
        $this->mauSupported = $mauSupported;

        return $this;
    }

    /**
     * Get mauSupported
     *
     * @return boolean
     */
    public function getMauSupported()
    {
        return $this->mauSupported;
    }

    /**
     * Set asn
     *
     * @param integer $asn
     * @return Switcher
     */
    public function setAsn($asn)
    {
        $this->asn = $asn;

        return $this;
    }

    /**
     * Get asn
     *
     * @return integer
     */
    public function getAsn()
    {
        return $this->asn;
    }

    /**
     * Set loopback IP
     *
     * @param string $loopback_ip
     * @return Switcher
     */
    public function setLoopbackIP($loopback_ip)
    {
        $this->loopback_ip = $loopback_ip;

        return $this;
    }

    /**
     * Get loopback IP
     *
     * @return string
     */
    public function getLoopbackIP()
    {
        return $this->loopback_ip;
    }

    /**
     * Set loopback name
     *
     * @param string $loopback_name
     * @return Switcher
     */
    public function setLoopbackName($loopback_name)
    {
        $this->loopback_name = $loopback_name;

        return $this;
    }

    /**
     * Get loopback name
     *
     * @return string
     */
    public function getLoopbackName()
    {
        return $this->loopback_name;
    }


    /**
     * @return string
     */
    public function getMgmtMacAddress()
    {
        return $this->mgmt_mac_address;
    }

    /**
     * @param string $mgmt_mac_address
     * @return Switcher
     */
    public function setMgmtMacAddress( $mgmt_mac_address ): Switcher
    {
        $this->mgmt_mac_address = $mgmt_mac_address;

        return $this;
    }

}
