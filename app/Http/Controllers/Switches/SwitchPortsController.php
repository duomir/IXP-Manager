<?php

namespace IXP\Http\Controllers\Switches;

/*
 * Copyright (C) 2009-2018 Internet Neutral Exchange Association Company Limited By Guarantee.
 * All Rights Reserved.
 *
 * This file is part of IXP Manager.
 *
 * IXP Manager is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation, version v2.0 of the License.
 *
 * IXP Manager is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License v2.0
 * along with IXP Manager.  If not, see:
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

use D2EM, Former, Redirect,Route, Validator;

use Entities\{
    Switcher            as SwitcherEntity,
    SwitchPort          as SwitchPortEntity
};

use Illuminate\Http\{
    RedirectResponse,
    JsonResponse,
    Request
};

use IXP\Http\Controllers\Doctrine2Frontend;

use IXP\Utils\View\Alert\{
    Alert,
    Container as AlertContainer
};

use Illuminate\View\View;
use OSS_SNMP\{
    Exception, Platform, SNMP
};


/**
 * Switch Port Controller
 * @author     Barry O'Donovan <barry@islandbridgenetworks.ie>
 * @author     Yann Robin <yann@islandbridgenetworks.ie>
 * @category   Controller
 * @copyright  Copyright (C) 2009-2018 Internet Neutral Exchange Association Company Limited By Guarantee
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU GPL V2.0
 */
class SwitchPortsController extends Doctrine2Frontend {

    /**
     * The object being added / edited
     * @var SwitchPortEntity
     */
    protected $object = null;


    /**
     * This function sets up the frontend controller
     */
    public function feInit(){

        $this->feParams         = (object)[

            'entity'            => SwitchPortEntity::class,
            'pagetitle'         => 'Switch Ports',

            'titleSingular'     => 'Switch Port',
            'nameSingular'      => 'a switch port',

            'listOrderBy'       => 'name',
            'listOrderByDir'    => 'ASC',

            'viewFolderName'    => 'switch-port',
            'route_action'      => 'list',

            'listColumns'       => [

                'id'        => [ 'title' => 'UID', 'display' => false ],

                'switch'  => [
                    'title'      => 'Switch',
                    'type'       => self::$FE_COL_TYPES[ 'HAS_ONE' ],
                    'controller' => 'switch',
                    'action'     => 'view',
                    'idField'    => 'switchid'
                ],

                'name'           => 'Description',
                'ifName'         => 'Name',
                'ifAlias'        => 'Alias',
                'active'       => [
                    'title'    => 'Active',
                    'type'     => self::$FE_COL_TYPES[ 'YES_NO' ],
                ],

                'type'  => [
                    'title'    => 'Type',
                    'type'     => self::$FE_COL_TYPES[ 'XLATE' ],
                    'xlator'   => SwitchPortEntity::$TYPES
                ]

            ]
        ];

        // display the same information in the view as the list
        $this->feParams->viewColumns = $this->feParams->listColumns;

    }


    /**
     * Additional routes
     *
     *
     * @param string $route_prefix
     * @return void
     */
    protected static function additionalRoutes( string $route_prefix )
    {
        // NB: this route is marked as 'read-only' to disable normal CRUD operations. It's not really read-only.

        Route::group( [  'prefix' => $route_prefix ], function() use ( $route_prefix ) {
            Route::get(  'unused-optics',   'Switches\SwitchPortsController@unusedOptics'   )->name( "switch-ports@unused-optics"   );
            Route::get(  'list-mau/{id}',   'Switches\SwitchPortsController@listMau'        )->name( "switch-ports@list-mau"        );
            Route::get(  'op-status/{id}',  'Switches\SwitchPortsController@listOpStatus'   )->name( "switch-ports@list-op-status"  );
            Route::get(  'snmp-poll/{id}',  'Switches\SwitchPortsController@snmpPoll'       )->name( "switch-ports@snmp-poll"       );
            Route::post( 'set-type',        'Switches\SwitchPortsController@setType'        )->name( "switch-ports@set-type"        );
            Route::post( 'delete',          'Switches\SwitchPortsController@delete'         )->name( "switch-ports@delete"          );
            Route::post( 'change-status',   'Switches\SwitchPortsController@changeStatus'   )->name( "switch-ports@change-status"   );
        });
    }


    /**
     * Provide array of rows for the list action and view action
     *
     * @param int $id The `id` of the row to load for `view` action`. `null` if `listAction`
     *
     * @return array
     */
    protected function listGetData( $id = null ) {
        return D2EM::getRepository( SwitchPortEntity::class )->getAllForFeList( $this->feParams, $id, $this->data );
    }


    public function list( Request $r  ) : View{

        if( $r->input( 'switch' )  !== null ) {
            /** @var SwitcherEntity $s */
            if(  $s = D2EM::getRepository( SwitcherEntity::class )->find( $r->input( 'switch' ) ) ) {
                $sid = $s->getId();
                $r->session()->put( "switch-configuration-switch", $sid );
            } else {
                $r->session()->remove( "switch-configuration-switch" );
                $sid = false;
            }
        } else if( $r->session()->exists( "switch-configuration-switch" ) ) {
            $sid = $r->session()->get( "switch-configuration-switch" );
        } else {
            $sid = false;
        }

        $this->data[ 'params' ][ 'switch' ]         = $sid;
        $this->data[ 'params' ][ 'switches' ]       = D2EM::getRepository( SwitcherEntity::class )->getNames();

        $this->data[ 'rows' ] = $this->listGetData();

        $this->setUpViews();

        $this->preList();

        return $this->display( 'list' );
    }

    /**
     * Display the form to add/edit an object
     *

     */
    protected function addForm( ) {
        $this->addEditSetup();
        $this->data[ 'params' ]['isAdd']        = true;
        $this->data[ 'params' ]['switches']     = D2EM::getRepository( SwitcherEntity::class  )->getNames();

        return $this->display( 'add-form' );
    }


    /**
     * Display the form to add/edit an object
     *
     * @param   int $id ID of the row to edit
     *
     * @return array
     */
    protected function addEditPrepareForm( $id = null ): array {

        $old = request()->old();

        if( $id !== null ) {

            if( !( $this->object = D2EM::getRepository( SwitchPortEntity::class )->find( $id) ) ) {
                abort(404, "Unknown Switch port");
            }

            Former::populate([
                'switchid'          => array_key_exists( 'switchid',    $old    ) ? $old['switchid']        :  $this->object->getSwitcher()->getId(),
                'name'              => array_key_exists( 'name',        $old    ) ? $old['name']            :  $this->object->getName(),
                'type'              => array_key_exists( 'type',        $old    ) ? $old['type']            :  $this->object->getType(),
                'active'            => array_key_exists( 'active',      $old    ) ? $old['active']          : ( $this->object->getActive() ? 1 : 0 ) ,
            ]);

        }



        return [
            'object'            => $this->object,
            'switches'          => D2EM::getRepository( SwitcherEntity::class  )->getNames(),
        ];
    }




    /**
     * Function to do the actual validation and storing of the submitted object.
     *
     * @param Request $request
     * @return bool|RedirectResponse
     *
     * @throws
     */
    public function doStore( Request $request ) {

        if( $request->input( "isAdd" ) ){

            $inputsArray = [
                'switchid'                  => 'required|integer|exists:Entities\Switcher,id',
                'numfirst'                  => 'required|integer|min:0',
                'numports'                  => 'required|integer|min:1|max:48',
                'type'                      => 'required|integer|in:' . implode( ',', array_keys( SwitchPortEntity::$TYPES ) ),
            ];

            for( $i = 0; $i < $request->input( 'numports' ); $i++ ) {
                $inputsArray[ 'portName'.$i ] = 'required|string|max:255';
                $inputsArray[ 'portType'.$i ] = 'required|integer|in:' . implode( ',', array_keys( SwitchPortEntity::$TYPES ) );
            }

            $validator = Validator::make( $request->all(), $inputsArray );


            if( $validator->fails() ) {
                return Redirect::back()->withErrors( $validator )->withInput();
            }

            for( $i = 0; $i < $request->input( 'numports' ); $i++ ) {
                $this->object = new SwitchPortEntity;
                D2EM::persist( $this->object );

                $this->object->setSwitcher( D2EM::getRepository( SwitcherEntity::class )->find( $request->input( "switchid" ) ) );
                $this->object->setType( $request->input('portType' . $i ) );
                $this->object->setName( $request->input('portName' . $i ) );
                $this->object->setActive( true );

            }


        } else {

            $validator = Validator::make( $request->all(), [
                    'switchid'                  => 'required|integer|exists:Entities\Switcher,id',
                    'name'                      => 'required|string|max:255',
                    'type'                      => 'required|integer|in:' . implode( ',', array_keys( SwitchPortEntity::$TYPES ) ),
                ]
            );


            if( $validator->fails() ) {
                return Redirect::back()->withErrors( $validator )->withInput();
            }

            if( !( $this->object = D2EM::getRepository( SwitchPortEntity::class )->find( $request->input( 'id' ) ) ) ) {
                abort(404, "Unknown Switch Port");
            }

            $this->object->setName(         $request->input( "name" ) );
            $this->object->setType(         $request->input( "type" ) );
            $this->object->setSwitcher(     D2EM::getRepository( SwitcherEntity::class )->find( $request->input( "switchid" ) ) );
            $this->object->setActive($request->input( "active" ) ?? 0 );
        }

        D2EM::flush();


        return true;
    }



    /**
     * @inheritdoc
     */
    protected function preDelete() : bool {

        if( ( $this->object->getPhysicalInterface() ) ) {

            $c = $this->object->getPhysicalInterface()->getVirtualInterface()->getCustomer();

            AlertContainer::push( "You cannot delete the switch port {$this->object->getName()} as it is assigned to a physical interface for "
                . "<a href=\"" . route('customer@overview', [ "id" => $c->getId(), "tab" => "ports" ]) . "\">{$c->getName()}</a>.", Alert::DANGER );
            return false;
        }

        return true;

    }

    /**
     * Set up all the information to display the Unused optics list
     *
     *
     * @bool
     */
    public function setUpUnusedOptics( ){

        $this->feParams->listOrderBy                = 'switch';
        $this->feParams->pagetitle                  = 'Switches';
        $this->feParams->pagetitlepostamble         = 'Unsed Optics';
        $this->feParams->route_prefix_page_title    = 'switch';

        $this->feParams->readonly                   = true;
        $this->feParams->hideactioncolumn           = true;

        $this->feParams->listColumns = [
            'ifIndex'       => [ 'title' => 'UID', 'display' => false ],
            'switch'        => 'Switch',
            'ifName'        => 'Port',
            'type'          => 'Type',

            'mauType'      => 'MAU Type',
            'mauState'     => 'MAU State',
            'mauJacktype'  => 'Jack Type',
        ];

        return true;
    }

    /**
     * Display the unused optics
     *
     * @return view
     *
     * @throws
     */
    public function unusedOptics( ) : View {

        $this->setUpUnusedOptics();

        $this->data[ 'rows' ] =  D2EM::getRepository( SwitchPortEntity::class )->getUnusedOpticsForFeList( $this->feParams );

        $this->setUpViews();
        $this->data[ 'view' ][ 'pageBreadcrumbs']       = $this->resolveTemplate( 'page-bread-crumbs',          false );

        $this->preList();

        return $this->display( 'list' );
    }

    /**
     * Set up all the information to display the Unused optics list
     *
     *
     * @bool
     */
    public function setUpListMau( ){

        $this->feParams->pagetitle                  = 'Switches';

        $this->feParams->route_prefix_page_title    = 'switch';
        $this->feParams->route_action               = 'list-mau';
        $this->feParams->listOrderBy                = 'id';

        $this->feParams->readonly                   = true;
        $this->feParams->hideactioncolumn           = true;

        $this->feParams->listColumns = [
            'id'                 => [ 'title' => 'UID', 'display' => true ],
            'ifName'             => 'Name',
            'type'                  => [
                'title'     =>  'Type',
                'type'      =>   self::$FE_COL_TYPES[ 'RESOLVE_CONST' ],
                'const'     =>   SwitchPortEntity::$TYPES,
            ],

            'state'       => [
                'title'     =>  'State (Admin/Op)',
                'type'      =>   self::$FE_COL_TYPES[ 'SCRIPT' ],
                'script'    =>   'switch-port/port-admin-status',
                'params'    =>   [
                                "adminState"    => "ifAdminStatus",
                                "operState"     => "ifOperStatus",
                ],
            ],

            'mauType'               => 'MAU Type',
            'mauState'              => 'MAU State',
            'mauAvailability'       => 'MAU Availability',
            'mauJacktype'           => 'Jack Type',

            'mauAutoNegAdminState'  => [
                'title'     =>  'Auto Neg',
                'type'      =>   self::$FE_COL_TYPES[ 'SCRIPT' ],
                'script'    =>   'switch-port/port-auto-neg',
                'params'    =>   [
                                "mauAutoNegAdminState"    => "mauAutoNegAdminState",
                            ],
            ]
        ];

        return true;
    }


    /**
     * Display the unused optics
     *
     * @param int $switchid
     *
     * @return view|redirect
     *
     * @throws
     */
    public function listMau( int $switchid = null ) {

        /** @var $s SwitcherEntity */
        if( !( $s = D2EM::getRepository( SwitcherEntity::class )->find( $switchid ) ) ){
            abort( "404", "Unknown Switch" );
        }

        $switches = [];
        $switchesList = D2EM::getRepository( SwitcherEntity::class )->findBy( [ 'mauSupported' => true ] );
        foreach( $switchesList as $switch ){
            $switches[ $switch->getId() ] = $switch->getName();
        }

        if( !$s->getMauSupported() ) {
            return redirect::to( route( "switch@list" ) );
        }

        $this->setUpListMau();

        $this->data[ 'rows' ] =  D2EM::getRepository( SwitchPortEntity::class )->getListMau( $this->feParams, $s->getId() );

        $this->feParams->pagetitlepostamble             = 'MAU Interface Detail for ' . $s->getName() ;

        $this->data[ 'params' ][ 'switches' ]           = $switches;
        $this->data[ 'params' ][ 'switch' ]             = $s->getId();

        $this->setUpViews();
        $this->data[ 'view' ][ 'pageBreadcrumbs']       = $this->resolveTemplate( 'page-bread-crumbs',          false );

        $this->preList();

        AlertContainer::push( "Data valid at time of last SNMP poll: " . $s->getLastPolled()->format( 'Y-m-d H:i:s' ), Alert::INFO );

        return $this->display( 'list' );

    }


    /**
     * Set up all the information to display the Unused optics list
     *
     * @bool
     */
    public function setUpOpStatus( ){

        $this->feParams->listOrderBy                = 'ifIndex';
        $this->feParams->pagetitle                  = 'Switches';

        $this->feParams->route_prefix_page_title    = 'switch';
        $this->feParams->route_action               = 'list-op-status';

        $this->feParams->listColumns = [
            'ifIndex'       => 'Index',
            'name'          => 'Description',
            'ifName'        => 'Name',
            'ifAlias'       => 'Alias',
            'lagIfIndex'    => 'LAG',
            'ifHighSpeed'   => 'Speed',
            'ifMtu'         => 'MTU',
            // 'ifPhysAddress' => 'Physical Address',

            'ifAdminStatus' => [
                'title'    => 'Admin State',
                'type'     => self::$FE_COL_TYPES[ 'SCRIPT' ],
                'script'   => 'switch-port/port-status',
                'params'    => [
                                "state"    => "ifAdminStatus",
                            ]
            ],

            'ifOperStatus' => [
                'title'    => 'Operational State',
                'type'     => self::$FE_COL_TYPES[ 'SCRIPT' ],
                'script'   => 'switch-port/port-status',
                'params'  => [
                                "state"    => "ifOperStatus",
                            ]
            ],
            'active'       => [
                    'title'    => 'Active',
                    'type'     => self::$FE_COL_TYPES[ 'YES_NO' ],
            ],
        ];

        return true;
    }

    /**
     * Display the switch ports operation status for a switch
     *
     * @param int $id
     *
     * @return view
     *
     * @throws
     */
    public function listOpStatus( int $id = null ){

        /** @var $s SwitcherEntity */
        if( $id && ( $s = D2EM::getRepository( SwitcherEntity::class )->find( $id ) ) ) {
            try // to refresh switch and switch port details via SNMP
            {

                $host = new SNMP( $s->getHostname(), $s->getSnmppasswd() );

                $s->snmpPoll( $host, true );

                $s->snmpPollSwitchPorts( $host, true );

                D2EM::flush();

                AlertContainer::push( "The below is <b>live information</b> gathered via SNMP", Alert::INFO );

            } catch( \OSS_SNMP\Exception $e ) {
                $lastpolled = is_null( $s->getLastPolled()) ? "never" : $s->getLastPolled()->format( 'Y-m-d H:i:s' );

                AlertContainer::push( "<b>Could not update switch and switch port details via SNMP poll.</b> " .
                    "Last successful poll: " . $lastpolled . "</b>.", Alert::DANGER );
            }
        }

        $this->setUpOpStatus();

        $this->data[ 'params' ][ 'portStates' ]     = \OSS_SNMP\MIBS\Iface::$IF_OPER_STATES;
        $this->data[ 'params' ][ 'switch' ]         = $s->getId();
        $this->data[ 'params' ][ 'switches']        = D2EM::getRepository( SwitcherEntity::class  )->getNames();

        $this->data[ 'rows' ] =  $this->listGetData();

        $this->feParams->pagetitlepostamble             = 'List Live Port State for ' . $s->getName() ;

        $this->setUpViews();

        return $this->display( 'list' );
    }



    /**
     * This action will find all ports on a switch, match them (where possible) to existing
     * ports of that switch in the database and allow the user to:
     *
     *  - view name (ifDescr), ifName and ifAlias
     *  - set the switchport type in bulk
     *  - remove port(s)
     *  - manage these actions in bulk (e.g. phpMyAdmin type row management)
     *
     *  Should this be in the SwitchController? Possibly...
     *
     */
    public function snmpPoll( int $id ){

        /** @var $s SwitcherEntity */
        if( $id && !( $s = D2EM::getRepository( SwitcherEntity::class )->find( $id ) ) ) {
            abort(404, "Unknown switch.");
        }

        if( !$s->getActive() ) {
            AlertContainer::push( "SNMP Polling of ports is only valid for switches that are active", Alert::DANGER );
            redirect::to( route( "switch@list" ) );
        }


        $results = [];

        try {
            //$host = new SNMP( $s->getHostname(), $s->getSnmppasswd() );
            $host = new SNMP( "switch1" , "switch1" );
            $s->snmpPoll( $host, true );
            $s->snmpPollSwitchPorts( $host, true, $results );
            D2EM::flush();

        } catch( \OSS_SNMP\Exception $e ) {
            AlertContainer::push( "Error polling switch via SNMP.", Alert::DANGER );
            redirect::to( route( "switch@list" ) );
        }


        return view( 'switch-port/snmp-poll' )->with([
            'switches'                  => D2EM::getRepository( SwitcherEntity::class )->getNames(),
            's'                         => $s,
            'ports'                     => $results,

        ]);

    }


    /**
     * Sets port type for port loaded
     *
     * @param   Request     $r          HTTP instance
     *
     * @return JsonResponse
     *
     * @throws
     */
    public function setType( Request $r ): JsonResponse {

        if( $r->input( "spid") ){
            foreach( $r->input( "spid") as $id ){
                /** @var $sp SwitchPortEntity */
                if( $id && !( $sp = D2EM::getRepository( SwitchPortEntity::class )->find( $id ) ) ) {
                    abort(404, "Unknown switch port.");
                }

                if( !array_key_exists( $r->input( "type"), SwitchPortEntity::$TYPES ) ){
                    return response()->json( [ 'success' => false ] );
                }

                $sp->setType( $r->input( "type") );
            }

            D2EM::flush();

            if( $r->input( "returnMessage") ){
                AlertContainer::push( "The selected switch ports have been updated", Alert::SUCCESS );
            }

            return response()->json( [ 'success' => true ] );

        }

    }


    /**
     * Sets port type for port loaded
     *
     * @param   Request     $r          HTTP instance
     *
     * @return JsonResponse
     *
     * @throws
     */
    public function delete( Request $r ): JsonResponse {

        if( $r->input( "spid") ){

            foreach( $r->input( "spid") as $id ){
                /** @var $sp SwitchPortEntity */
                if( $id && !( $sp = D2EM::getRepository( SwitchPortEntity::class )->find( $id ) ) ) {
                    abort(404, "Unknown switch port.");
                }

                if( $sp->getPhysicalInterface() ){
                    $cust = $sp->getPhysicalInterface()->getVirtualInterface()->getCustomer();
                    AlertContainer::push( "Could not delete switch port {$sp->getName()} as it is assigned to a physical interface for "
                        . "<a href=\""
                        . route( "customer@overview" , [ 'id' => $cust->getId(), 'tab' => 'ports' ]  )
                        . "\">{$cust->getName()}</a>.", Alert::DANGER
                    );

                } else{
                    D2EM::remove( $sp );
                }

            }

            D2EM::flush();

            AlertContainer::push(
                "<b>Please Note:</b> It is not possible to delete real physical Ethernet switch ports as "
                . "the switch is re-polled and these ports are added back into the system as new ports automatically. "
                . "The purpose of delete is to remove ports that were manually added to the database that do not match "
                . "up with phsyical ports on the switch. You can deactivate switchports however.",
                Alert::INFO
            );

            AlertContainer::push( "The selected switch ports have been deleted", Alert::SUCCESS );

            return response()->json( [ 'success' => true ] );

        }

    }

    /**
     * Change the port status for the Switch Ports
     *
     * @param   Request     $r          HTTP instance
     *
     * @return JsonResponse
     *
     * @throws
     */
    public function changeStatus( Request $r ): JsonResponse {

        if( $r->input( "spid") ){
            foreach( $r->input( "spid") as $id ){
                /** @var $sp SwitchPortEntity */
                if( $id && !( $sp = D2EM::getRepository( SwitchPortEntity::class )->find( $id ) ) ) {
                    abort(404, "Unknown switch port.");
                }

                $sp->setActive( $r->input( "active") ? 1 : 0 );
            }

            D2EM::flush();

            AlertContainer::push(  "The selected switch ports have been updated.", Alert::SUCCESS );

            return response()->json( [ 'success' => true ] );

        }

    }



    private function setUpViews(){
        $this->data[ 'view' ][ 'listEmptyMessage']      = $this->resolveTemplate( 'list-empty-message',         false );
        $this->data[ 'view' ][ 'listHeadOverride']      = $this->resolveTemplate( 'list-head-override',         false );
        $this->data[ 'view' ][ 'listRowOverride']       = $this->resolveTemplate( 'list-row-override',          false );
        $this->data[ 'view' ][ 'listPreamble']          = $this->resolveTemplate( 'list-preamble',              false );
        $this->data[ 'view' ][ 'listPostamble']         = $this->resolveTemplate( 'list-postamble',             false );
        $this->data[ 'view' ][ 'listRowMenu']           = $this->resolveTemplate( 'list-row-menu',              false );
        $this->data[ 'view' ][ 'pageHeaderPreamble']    = $this->resolveTemplate( 'page-header-preamble',       false );
        $this->data[ 'view' ][ 'listScript' ]           = $this->resolveTemplate( 'js/list' );
    }



}
