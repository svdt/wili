import requests
from collections import Counter

class RBL:
        id = 0
        line = ''
        station = ''
        towards = ''
        direction = ''
        time = -1

def getTime(rblid):
        apikey = 'bny4N7ebcZNHnNVw'
        apiurl = 'https://www.wienerlinien.at/ogd_realtime/monitor?rbl={rbl}&sender={apikey}'
        st = 1

        rblid = list(set(rblid))
        rbls = []
        for id in rblid:
                url = apiurl.replace('{apikey}', apikey).replace('{rbl}', id)
                try:
                    r = requests.get(url)
                except requests.exceptions.ConnectionError:
                    return None
                except requests.exceptions.Timeout:
                    return None
                except requests.exceptions.HTTPError:
                    return None
                except:
                    return []
                if r.status_code == 200:
                        R = r.json()['data']['monitors']
                        for m in R:
                            try:
                                    rbl = RBL()
                                    rbl.id = id
                                    rbl.line = m['lines'][0]['name']
                                    rbl.station = m['locationStop']['properties']['name']
                                    rbl.direction = m['lines'][0]['richtungsId']
                                    #rbl.towards = m['lines'][0]['towards']
                                    rbl.time = []
                                    if m['lines'][0]['realtimeSupported']:
                                        for d in m['lines'][0]['departures']['departure']:
                                            rbl.time.append(d['departureTime']['countdown'])
                                    #print rbl.line, rbl.direction, rbl.time
                                    #dumpRBL(rbl)
                                    rbls.append(rbl)
                            except:
                                print("some error occurred try next one at RBL "+str(id)+" line "+str(m['lines'][0]['name']))
        return rbls
