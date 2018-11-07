/***************************************************************************
 * This file is part of Roundcube "settings" plugin.              
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

$(document).ready(function(){if("larry"==rcmail.env.skin&&$(".minmodetoggle").get(0)){var a=rcmail.get_cookie("minimalmode");(parseInt(a)||null===a&&850>$(window).height())&&$("#mainscreen").css("top","55px");$(window).resize(function(){var a=rcmail.get_cookie("minimalmode");parseInt(a)||null===a&&850>$(window).height()?$("#mainscreen").css("top","55px"):$("#mainscreen").css("top","132px")})}parent.location.href!=document.location.href&&("larry"==rcmail.env.skin?$(".formbuttons").hide():$("#formfooter").hide())});