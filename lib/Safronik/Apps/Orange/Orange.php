<?php

namespace Safronik\Apps\Orange;

// Interfaces
use Safronik\Core\CodeTemplates\Installer;
use Safronik\Core\CodeTemplates\Interfaces\Installable;

// Useful
use Safronik\Services\DB\DB;
use Safronik\Services\DB\Gateways\DBGatewayDBStructure;
use Safronik\Services\DBStructureHandler\DBStructureHandler;
use Safronik\Services\Request\Request;
use Safronik\Services\Services;
use Safronik\Services\User\CurrentUser;
use Safronik\Services\Visitor\Visitor;

/**
 * Service
 * Application
 * Block
 * Element
 *
 * @property DB          $db
 * @property Visitor     $visitor
 * @property CurrentUser $user
 * @property Request     $request
 */
final class Orange extends \Safronik\Apps\App implements Installable
{
    use Installer;
    
    protected static string $slug = 'cms';
    protected int $inner_request_nonce = 0x0; // Hex format
    public    array $apps;
    
    public function __construct( ...$params )
    {
        parent::__construct( ...$params );
    }
    
    public function installDB(): void
    {
        $installer = new \Safronik\Apps\Installer\Installer(
            self::class,
            Services::get( 'db.structure' )
        );
    
        $installer->update();
    }
    
    public function request( $url ): array
    {
        $curl = curl_init();
        
        curl_setopt_array( $curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => [
                'token: 64f192dde162c64f192dde1630',
            ],
        ] );
        
        $result = curl_exec( $curl );
        
        curl_close( $curl );
        
        return json_decode( $result, true );
    }
    
    public function fillCompanyTable()
    {
        $companies = $this->request( 'https://datsorange.devteam.games/companies' );
        
        /** @var DB $db */
        $db = Services::get( 'db' );
        
        foreach( $companies as $company ){
            $db->insert( 'companies', [
                'id'     => [ $company['id'], 'int' ],
                'ticker' => [ $company['ticker'], 'string' ],
            ] );
        }
    }

    function fillNewsTable()
    {
        $news = $this->request( 'https://datsorange.devteam.games/news/LatestNews5Minutes' );
        
        /** @var DB $db */
        $db = Services::get( 'db' );
        
        $all_companies = [];
        foreach( $news as $new ){
            $company_ids = [];
    
            $all_companies = array_merge( $all_companies, $new['companiesAffected'] );
            
            foreach( $new['companiesAffected'] as $company ){
                $sql_result = $db->select( 'companies', [ 'id' ], [ 'ticker' => [ $company,] ] );
                if( $sql_result ){
                    $company_ids = $sql_result['id'];
                }
            }
    
            
            
            $company_ids
                && $db->insert( 'news', [
                    'companiesAffected' => [ json_encode( $company_ids ), 'STR' ],
                    'date'              => [ $new['date'], 'STR' ],
                    'rate'              => [ $new['rate'], 'INT' ],
                    'text'              => [ $new['text'], 'STR' ],
                ] );
        }
    }
    
    public function getPrices()
    {
        $prices = $this->request( 'https://datsorange.devteam.games/companies' );
        
        /** @var DB $db */
        $db = Services::get( 'db' );
        
        foreach( $prices as $price ){
            $db->insert( 'prices', [
                'id'     => [ $price['id'], 'int' ],
                'ticker' => [ $price['ticker'], 'string' ],
                'price'  => [ $price['price'], 'float' ],
            ] );
        }
    }
    
    public function calculateCurrentPrice( $bids )
    {
        $total = 0;
        $count = 0;
        
        
        foreach( $bids as $bid ){
            $this->cutGiantPrice( $bid );
            $total += $bid['price'] * $bid['quantity'];
            $count += $bid['quantity'];
        }
        
        return $total / $count;
    }

    
    public function cutGiantPrice( &$bids )
    {
        if( count( $bids ) > 3 ){
            
            $prices = array_column( $bids, 'price' );
    
            unset( $bids[ array_search( min( $prices ), array_column( $bids, 'price' ) ) ] );
            unset( $bids[ array_search( max( $prices ), array_column( $bids, 'price' ) ) ] );
            
            $this->cutGiantPrice( $bids );
        }
        
        return $bids;
    }
    
    public function updateStrategies()
    {
        $demand = $this->request( 'https://datsorange.devteam.games/sellStock' );
        
        foreach($demand as $demand_item){
            
            if( ! $demand_item['bids'] ){
                continue;
            }
            
            $current_price = $this->calculateCurrentPrice( $demand_item['bids'] );
            
            $item = [
                'id'            => [ $demand_item['id'], 'integer' ],
                'ticker'        => [ $demand_item['ticker'], 'string' ],
                'current_price' => [ $current_price, 'integer' ],
                'median_price'  => [ $current_price, 'integer' ],
                'trend'         => [ 0, 'integer'],
            ];
            
            $current_strategy = $this->db->select( 'short_strategies', [], [ 'id' => [$item['id'][0], 'integer'] ] );
            
            if( $current_strategy ){
                
                if( time() > strtotime($current_strategy['created']) + 60 * 2 ){
                    $this->db->delete( 'short_strategies', [ 'id' => [$item['id'][0], 'integer'] ]);
                }
                
                if( $current_strategy['median_price'] ){
                    $item['median_price'][0] = ( $current_strategy['median_price'] + $item['current_price'][0] ) / 2;
                }
                
                if( $current_strategy['median_price'] ){
                    
                    $price_delta = $item['current_price'][0] - $current_strategy['median_price'];
                    $price_median_percent = round($current_strategy['median_price'] / 100, 2 );
                    $item['trend'][0] = round(
                        $price_delta / $price_median_percent,
                        2
                    );
                    
                    if( $current_strategy['trend'] ){
                        $item['trend'][0] = ( $current_strategy['trend'] + $item['trend'][0] ) / 2;
                    }
                }
                
                $this->db->update( 'short_strategies', $item, [ 'id' => [$item['id'][0], 'integer' ] ] );
                
                continue;
            }
            
            $this->db->insert( 'short_strategies', $item );
        }
        
        return [];
    }
}