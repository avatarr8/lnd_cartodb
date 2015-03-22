# CartoDB implementation for LND
This document is meant to reflect how a cartoDB map works on the Living New Deal site. The implementation has 3 parts:

1. A cartoDB data set and visualization that lives on the CartoDB servers
2. A set of page_carto additions to the 
3. Hooks added to the LivingNewDeal theme that connect to the cartoDB data set to add new rows and update old entries.


## 1 CARTODB INFORMATION

1. Data
2. Default settings
3. CartoCSS

## 2 CUSTOM CARTO PAGE in LND THEME
1. In **header-carto.php**, line 25 adds the carto CSS
2. In **page-carto.php**, lines 7-17 pull the URL for the map from the custom field "carto_source" that has to be filled in via the dashboard.

###To Implement###

1. Set default map if the carto_source isn't set in the dasbhoard
2. I've added a "style: height:100%" to the body, container and map tags so that the map displays properly. This should probably be done in CSS or LESS at some point.
3. Add search features


## 3 HOOKS & FUNCTIONS TO UPDATE CARTODB
