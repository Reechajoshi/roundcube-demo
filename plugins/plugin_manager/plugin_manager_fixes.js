/***************************************************************************
 * This file is part of Roundcube "plugin_manager" plugin.              
 *                                                                 
 * Your are not allowed to distribute this file or parts of it.    
 *                                                                 
 * This file is distributed in the hope that it will be useful,    
 * but WITHOUT ANY WARRANTY; without even the implied warranty of  
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.          
 *                                                                 
 * Copyright (c) 2012 - 2014 Roland 'Rosali' Liebl - all rights reserved
 * dev-team [at] myroundcube [dot] com
 * http://myroundcube.com
 ***************************************************************************/

$(document).ready(function(){"larry"==rcmail.env.skin&&($("#toplogo").attr("onclick",""),$("#toplogo").click(function(){$(".button-mail").click()}),$("#toplogo").attr("style","cursor: pointer"))});