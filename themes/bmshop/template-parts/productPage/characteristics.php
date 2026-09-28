<?php
		
				$weight = get_post_meta( get_the_ID(), '_weight', true );
				$characteristicsText = get_post_meta(get_the_ID(), '_characteristics_text_field',true);
				$characteristicsCheckbox = get_post_meta(get_the_ID(), '_characteristics_checkbox_field',true);
                
				
                if( $characteristicsText || $characteristicsCheckbox ){ ?>
                    
                    
                    <table class="detailed-fields">
                        
                        <?php if( $weight ){ ?>
                        <tr>
    						    
    						<td class="detailed-fields-key"> 
    						
    							<div class="fieldsName"><span>ВЕС</span></div>
    							    
    						</td>
    							
    						<td class="detailed-fields-value"> <?php echo $weight; ?> кг </td>
    										            
    					</tr>
                        <?php } ?>
                        
                         <?php foreach($characteristicsText as $key=>$value){ 
										        
        						    $term = get_term_by(id,$key,"characteristics");
                        
                                    if(!$term){
                                        continue;
                                    }
                                    
                                    $productDescr = $term->description;
									$fancybox = 'data-fancybox data-src="#descr'.$term->term_id.'"';      
						  ?>
										        
    						<tr>
    						    
    							<td class="detailed-fields-key<?=($productDescr)?' detailed-fields-keyDescr':'' ?>"> 
    							    <div <?=($productDescr)?$fancybox:'' ?> class="fieldsName"><span><?php echo $term->name; ?></span></div>
    							    
    							    <?php if($productDescr){ ?>
    							        
    							        <div id="descr<?=$term->term_id ?>" class="fieldDescription"><?=$productDescr; ?>  </div>
    							        
    							    <?php } ?>
    							    
    							</td>
    							
    							<td class="detailed-fields-value"> <?php echo $value; ?>  </td>
    										            
    						</tr>
										        
						  <?php } ?>
						  
						  
						  <?php foreach($characteristicsCheckbox as $key=>$characteristicsValues){ 
										        
        						    $term = get_term_by(id,$key,"characteristics");
                        
                                    if(!$term){
                                        continue;
                                    }
                                    
                                    $productDescr = $term->description;
									$fancybox = 'data-fancybox data-src="#descr'.$term->term_id.'"'; 	        
						  ?>
										        
    						<tr>
    							<td class="detailed-fields-key<?=($productDescr)?' detailed-fields-keyDescr':'' ?>"> 
    							    <div <?=($productDescr)?$fancybox:'' ?> class="fieldsName"><span><?php echo $term->name; ?></span></div>
    							    
    							    <?php if($productDescr){ ?>
    							        
    							        <div id="descr<?=$term->term_id ?>" class="fieldDescription"><?=$productDescr; ?>  </div>
    							        
    							    <?php } ?>
    							    
    							</td>
    							
    							<td class="detailed-fields-value"> 
    							
        							<?php foreach($characteristicsValues as $key=>$value){ 
    										        
                						    $term = get_term_by(id,$key,"characteristics");
                                
                                            if(!$term){
                                                continue;
                                            }
        						     
        						            echo $term->name;
        						            if ( next($characteristicsValues)==true ) echo ", ";
        										        
        						    } ?>
    							
    							</td>
    										            
    						</tr>
										        
						  <?php } ?>
                        
                    </table>
                
                <?php
                    
                }else{
                    ?>
                    
                    <p>Характеристик нет</p>
                    
                    <?php
                }