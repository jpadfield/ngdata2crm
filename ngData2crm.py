#!/usr/bin/python3

import requests
import json
import os
from rdflib import Graph, Literal, URIRef, Namespace
from rdflib.namespace import RDF, RDFS, XSD
from urllib.parse import quote
import sys
from datetime import datetime
import logging

#### from chat GPT

def generate_date_label(date_dict):
    # Function to convert a date string to a datetime object
    def parse_date(date_str):
        try:
            # Try to parse as timestamp
            return datetime.fromtimestamp(float(date_str))
        except ValueError:
            # Try to parse as different date formats
            for fmt in ('%Y-%m-%d', '%Y-%m-%d %H:%M:%S', '%Y', '%Y-%m-%dT%H:%M'):
                try:
                    return datetime.strptime(date_str, fmt)
                except ValueError:
                    pass
            raise ValueError(f"Unknown date format: {date_str}")

    # Check if 'value' field exists, if not, generate it
    if 'value' not in date_dict:
        from_date = parse_date(date_dict['from'])
        to_date = parse_date(date_dict['to'])
        date_label = f"{from_date.strftime('%Y-%m-%d')} to {to_date.strftime('%Y-%m-%d')}"
        date_dict['value'] = date_label

    return date_dict
    
#### from claude

# Function to fetch data from URL
def fetch_data_from_url_OLD(url):
    response = requests.get(url)
    if response.status_code == 200:
        return response.json()
    else:
        raise Exception(f"Failed to fetch data: HTTP {response.status_code}")

# Configure logging
logging.basicConfig(level=logging.DEBUG, format='%(asctime)s - %(levelname)s - %(message)s')

def fetch_data_from_url(url):
    headers = {
        'User-Agent': ('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 '
                       '(KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'),
        'Accept': 'application/json, text/plain, */*',
        'Accept-Language': 'en-US,en;q=0.9',
        'Connection': 'keep-alive',
    }

    logging.debug(f"Attempting to fetch data from URL: {url}")
    
    try:
        response = requests.get(url, headers=headers, timeout=30, verify=True)  # Increased timeout to 30 seconds
        logging.debug(f"Received response with status code: {response.status_code}")
        
        if response.status_code == 200:
            logging.debug("Successful response, attempting to parse JSON")
            return response.json()
        else:
            logging.error(f"Failed to fetch data: HTTP {response.status_code}")
            raise Exception(f"Failed to fetch data: HTTP {response.status_code}")
    
    except requests.exceptions.Timeout:
        logging.error("Request timed out")
        raise Exception("Request timed out")
    
    except requests.exceptions.RequestException as e:
        logging.error(f"Request failed: {e}")
        raise Exception(f"Request failed: {e}")
    
    except Exception as e:
        logging.error(f"An unexpected error occurred: {e}")
        raise
	
# Create the graph
g = Graph()

# Define namespaces
crm = Namespace("http://www.cidoc-crm.org/cidoc-crm/")
ng = Namespace("https://data.ng.ac.uk/")
ng_vocab = Namespace("https://vocab.ng.ac.uk/")

# Add namespace prefixes for readability
g.bind("crm", crm)
g.bind("ng", ng)
g.bind("ng_vocab", ng_vocab)

# Load or create the reference JSON document
ref_json_path = "reference_pids.json"
if os.path.exists(ref_json_path):
    with open(ref_json_path, 'r') as f:
        reference_pids = json.load(f)
else:
    reference_pids = {}

# Function to create or retrieve URIs for entities
def create_uri(entity_type, parent_pid=None, identifier=None, label=None):
    if parent_pid:
        pid = f"{parent_pid}/{entity_type}-{identifier}"
    elif identifier and identifier.startswith("AUTO-"):
        pid = identifier
    else:
        pid = f"AUTO-{len(reference_pids):04d}-0000-0000"
        while pid in reference_pids:
            pid = f"AUTO-{int(pid[5:9])+1:04d}-0000-0000"
    
    if pid not in reference_pids:
        reference_pids[pid] = {"type": entity_type, "parent": parent_pid, "identifier": identifier, "label": label}
        
    encoded_pid = quote(pid, safe=":/")
    
    return ng[encoded_pid]

# Function to add identifiers
def add_identifiers(subject_uri, identifiers):
    for identifier in identifiers:
        id_value = identifier['value']
        id_type = identifier['type']
        id_uri = create_uri("E42", None, f"identifier_{id_type}_{id_value}", f"Identifier: {id_value}")
        g.add((id_uri, RDF.type, crm.E42_Identifier))
        g.add((id_uri, RDFS.label, Literal(id_value)))
        g.add((id_uri, crm.P2_has_type, ng_vocab[id_type.replace(" ", "_")]))
        g.add((subject_uri, crm.P1_is_identified_by, id_uri))

# Function to add time span
def add_time_span(event_uri, date_info):
  
    ## check for a display value and add if missing
    date_info = generate_date_label(date_info)

    time_span_uri = create_uri("E52", None, f"timespan_{date_info['from']}_{date_info.get('to', '')}", f"Time Span: {date_info['value']}")
    g.add((time_span_uri, RDF.type, crm.E52_Time_Span))
    g.add((time_span_uri, RDFS.label, Literal(date_info['value'])))
    g.add((time_span_uri, crm.P82a_begin_of_the_begin, Literal(date_info['from'], datatype=XSD.date)))
    if 'to' in date_info:
        g.add((time_span_uri, crm.P82b_end_of_the_end, Literal(date_info['to'], datatype=XSD.date)))
    g.add((event_uri, crm.P4_has_time_span, time_span_uri))

# Main mapping function
def map_to_cidoc_crm(hit):
    data = hit['_source']
    pid = hit['_id']
    
    # Create the main object (E22 Human-Made Object)
    object_uri = ng[pid]
    g.add((object_uri, RDF.type, crm.E22_Human_Made_Object))
    
    # Add title
    title = data['title'][0]['value']
    g.add((object_uri, crm.P102_has_title, Literal(title)))
    
    # Add all identifiers
    add_identifiers(object_uri, data['identifier'])
    
    # Add creation event
    if 'creation' in data:
        creation_event = create_uri("E65", pid, "creation", f"Creation of {title}")
        g.add((creation_event, RDF.type, crm.E65_Creation))
        g.add((object_uri, crm.P108i_was_produced_by, creation_event))
        
        # Add creator
        if 'maker' in data['creation'][0]:
            creator = data['creation'][0]['maker'][0]['summary']['title']
            creator_uri = create_uri("E21", None, data['creation'][0]['maker'][0]['@admin']['uid'], creator)
            g.add((creator_uri, RDF.type, crm.E21_Person))
            g.add((creator_uri, RDFS.label, Literal(creator)))
            g.add((creation_event, crm.P14_carried_out_by, creator_uri))
            
            # Add creator's identifiers
            if '@admin' in data['creation'][0]['maker'][0]:
                add_identifiers(creator_uri, [
                    {'type': 'uid', 'value': data['creation'][0]['maker'][0]['@admin']['uid']},
                    {'type': 'id', 'value': data['creation'][0]['maker'][0]['@admin']['id']},
                    {'type': 'uuid', 'value': data['creation'][0]['maker'][0]['@admin']['uuid']}
                ])
        
        # Add creation date
        if 'date' in data['creation'][0]:
            add_time_span(creation_event, data['creation'][0]['date'][0])

    # Add material
    if 'material' in data:
        material = data['material'][0]['value']
        g.add((object_uri, crm.P45_consists_of, Literal(material)))

    # Add measurements
    if 'measurements' in data:
        for measurement in data['measurements']:
            if measurement['type'] == 'Overall':
                for dimension in measurement['dimensions']:
                    measurement_uri = create_uri("E54", pid, f"dimension_{dimension['dimension']}", f"{dimension['dimension']} of {title}")
                    g.add((measurement_uri, RDF.type, crm.E54_Dimension))
                    g.add((measurement_uri, crm.P90_has_value, Literal(dimension['value'], datatype=XSD.decimal)))
                    g.add((measurement_uri, crm.P91_has_unit, Literal(dimension['units'])))
                    g.add((object_uri, crm.P43_has_dimension, measurement_uri))
                    
                    # Add dimension type
                    dimension_type_uri = ng_vocab[dimension['dimension'].lower()]
                    g.add((measurement_uri, crm.P2_has_type, dimension_type_uri))

    # Add current location
    if 'location' in data and 'current' in data['location']:
        location = data['location']['current']['summary']['title']
        location_uri = create_uri("E53", None, data['location']['current']['@admin']['uid'], location)
        g.add((location_uri, RDF.type, crm.E53_Place))
        g.add((location_uri, RDFS.label, Literal(location)))
        g.add((object_uri, crm.P55_has_current_location, location_uri))
        
        # Add location identifiers
        if '@admin' in data['location']['current']:
            add_identifiers(location_uri, [
                {'type': 'uid', 'value': data['location']['current']['@admin']['uid']},
                {'type': 'id', 'value': data['location']['current']['@admin']['id']},
                {'type': 'uuid', 'value': data['location']['current']['@admin']['uuid']}
            ])
        
        # Add location date
        if 'date' in data['location']['current']['@link']:
            location_event_uri = create_uri("E9", pid, f"location_assignment_{location}", f"Assignment of {title} to {location}")
            g.add((location_event_uri, RDF.type, crm.E9_Move))
            g.add((location_event_uri, crm.P25_moved, object_uri))
            g.add((location_event_uri, crm.P26_moved_to, location_uri))
            add_time_span(location_event_uri, data['location']['current']['@link']['date'][0])

    return g

# Main function to process URL and map data
def process_url_and_map(url):
    elasticsearch_data = fetch_data_from_url(url)
    
    for hit in elasticsearch_data['hits']['hits']:
        map_to_cidoc_crm(hit)
    
    return g
    
# Main function to process URL and map data
def process_data_and_map(data):
    
    for hit in data['hits']['hits']:
        map_to_cidoc_crm(hit)
    
    return g

if __name__ == "__main__":
    input_json = json.load(sys.stdin)
    
    result_graph = process_data_and_map(input_json)
    rdf_output = result_graph.serialize(format="turtle")
    
    print(rdf_output)
   
