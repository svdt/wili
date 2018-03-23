#!/bin/bash
DIR=$(dirname "${BASH_SOURCE[0]}")
((count = 10))
while [[ $count -ne 0 ]] ; do
    ping -c 1 data.wien.gv.at > /dev/null
    rc=$?
    if [[ $rc -eq 0 ]] ; then
        ((count = 1))
    fi
    ((count = count - 1))
done

if [[ $rc -eq 0 ]] ; then
    wget http://data.wien.gv.at/csv/wienerlinien-ogd-haltestellen.csv -O $DIR/wldata/wienerlinien-ogd-haltestellen.csv
    wget http://data.wien.gv.at/csv/wienerlinien-ogd-linien.csv -O $DIR/wldata/wienerlinien-ogd-linien.csv
    wget http://data.wien.gv.at/csv/wienerlinien-ogd-steige.csv -O $DIR/wldata/wienerlinien-ogd-steige.csv
    exit 0
fi
exit 1
