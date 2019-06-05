<?php
/*
 * Copyright 2012-2019 Damien Seguy – Exakat SAS <contact(at)exakat.io>
 * This file is part of Exakat.
 *
 * Exakat is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Exakat is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Exakat.  If not, see <http://www.gnu.org/licenses/>.
 *
 * The latest code can be found at <http://exakat.io/>.
 *
*/


namespace Exakat\Tasks\LoadFinal;

use Exakat\Analyzer\Analyzer;
use Exakat\Query\Query;

class SetClassPropertyRemoteDefinition extends LoadFinal {
    public function run() {

        // For properties in traits
        $query = $this->newQuery('SetClassPropertyRemoteDefinition property');
        $query->atomIs('Trait', Analyzer::WITHOUT_CONSTANTS)
              ->outIs('PPP')
              ->atomIs('Ppp', Analyzer::WITHOUT_CONSTANTS)
              ->savePropertyAs('fullcode', 'doublon')
              ->_as('source')
              ->back('first')
              ->raw(<<<'GREMLIN'
repeat( __.out("DEFINITION").in("USE").in("USE")).emit().times(15).hasLabel("Class")
GREMLIN
,array(), array())
              ->atomIs('Class', Analyzer::WITHOUT_CONSTANTS)
              ->_as('classe')
              // weird : without this deduplication, it creates twice the PPP
              ->not(
                $query->side()
                      ->outIs('PPP')
                      ->samePropertyAs('fullcode', 'doublon', Analyzer::CASE_SENSITIVE)
                      ->prepareSide()
              )
              ->raw(<<<GREMLIN
addV()
      .property(label, "Ppp").as("clone")
      .sideEffect(
        select("source").properties().as("p")
        .select("clone")
          .property(select("p").key(), select("p").value())
          .property("virtual", true)
      )
      .addE("PPP").from("classe")
    
      .select("source").where( 
        __.out("TYPEHINT").as("sourcetypehint")
          .addV()
          .property(label, select("sourcetypehint").label()).as("clonetypehint")
          .property("virtual", true)
          .sideEffect(
            select("sourcetypehint").properties().as("p")
            .select("clonetypehint")
              .property(select("p").key(), select("p").value())
           )
          .addE("TYPEHINT").from("clone")
          .fold()
    )
    .select("source").where( 
        __.out("PPP").as("sourceppp")
          .addV()
            .property(label, "Propertydefinition")
            .property("virtual", true).as("cloneppp")
          .sideEffect(
            select("sourceppp").properties().as("p")
            .select("cloneppp")
              .property(select("p").key(), select("p").value())
          )
          .addE("PPP").from("clone")
      
          .select("sourceppp").where( 
            __.out("DEFAULT").not(where(__.in("RIGHT"))).as("sourcedefault")
              .addV()
              .property(label, select("sourcedefault").label()).as("clonedefault")
              .property("virtual", true)
              .sideEffect(
                select("sourcedefault").properties().as("p").
                select("clonedefault")
                    .property(select("p").key(), select("p").value())
                )
              .addE("DEFAULT").from("cloneppp")
              .fold()
            )
           .fold()
        ).fold()


GREMLIN
,array(), array())
              ->returnCount();
        $query->prepareRawQuery();
        $result = $this->gremlin->query($query->getQuery(), $query->getArguments());
        $countReports = $result->toInt();
        display("Added $countReports traits property to class definitions");

        // For properties in classes
        $query = $this->newQuery('SetClassPropertyRemoteDefinition class property');
        $query->atomIs('Class', Analyzer::WITHOUT_CONSTANTS)
              ->outIs('PPP')
              ->isNot('visibility', 'private')
              ->atomIs('Ppp', Analyzer::WITHOUT_CONSTANTS)
              ->savePropertyAs('fullcode', 'doublon')
              ->_as('source')
              ->back('first')
              ->outIs('DEFINITION')
              ->inIs('EXTENDS')
              ->atomIs('Class', Analyzer::WITHOUT_CONSTANTS)
              ->_as('classe')
              // weird : without this deduplication, it creates twice the PPP
              ->not(
                $query->side()
                      ->outIs('PPP')
                      ->samePropertyAs('fullcode', 'doublon', Analyzer::CASE_SENSITIVE)
                      ->prepareSide()
              )
              ->raw(<<<GREMLIN
addV()
      .property(label, "Ppp").as("clone")
      .sideEffect(
        select("source").properties().as("p")
        .select("clone")
          .property(select("p").key(), select("p").value())
          .property("virtual", true)
      )
      .addE("PPP").from("classe")
      
      .select("source").where( 
        __.out("TYPEHINT").as("sourcetypehint")
          .addV()
          .property(label, select("sourcetypehint").label()).as("clonetypehint")
          .property("virtual", true)
          .sideEffect(
            select("sourcetypehint").properties().as("p")
            .select("clonetypehint")
              .property(select("p").key(), select("p").value())
           )
          .addE("TYPEHINT").from("clone")
          .fold()
    )
    .select("source").where( 
        __.out("PPP").as("sourceppp")
          .addV()
            .property(label, "Propertydefinition")
            .property("virtual", true).as("cloneppp")
          .sideEffect(
            select("sourceppp").properties().as("p")
            .select("cloneppp")
              .property(select("p").key(), select("p").value())
          )
          .addE("PPP").from("clone")

          .select("sourceppp").addE("OVERWRITE").from("cloneppp")

          .sideEffect(
            select('sourceppp').outE().hasLabel('DEFINITION').as('e')
                .where( select('e').inV().in('LEFT').in('EXPRESSION').in("BLOCK").hasLabel('Method').not(has('visibility', 'private')))
                .select('cloneppp')
                .addE(select('e').label()).as('eclone')
                .to(select('e').inV())
            )

          .select("sourceppp").where( 
            __.out("DEFAULT").not(where(__.in("RIGHT"))).as("sourcedefault")
              .addV()
              .property(label, select("sourcedefault").label()).as("clonedefault")
              .property("virtual", true)
              .sideEffect(
                select("sourcedefault").properties().as("p").
                select("clonedefault")
                    .property(select("p").key(), select("p").value())
                )
              .addE("DEFAULT").from("cloneppp")
              .fold()
            )
           .fold()
        ).fold()


GREMLIN
,array(), array())
              ->returnCount();
        $query->prepareRawQuery();
        $result = $this->gremlin->query($query->getQuery(), $query->getArguments());
        $countReports = $result->toInt();
        display("Added $countReports parent class property to class definitions");

        // For static properties calls, in traits
        $query = $this->newQuery('SetClassPropertyRemoteDefinition property');
        $query->atomIs('Staticproperty', Analyzer::WITHOUT_CONSTANTS)
              ->_as('property')
              ->hasNoIn('DEFINITION')
              ->outIs('MEMBER')
              ->atomIs('Staticpropertyname', Analyzer::WITHOUT_CONSTANTS)
              ->savePropertyAs('code', 'name')
              ->inIs('MEMBER')
              ->outIs('CLASS')
              ->inIs('DEFINITION')
              ->atomIs(array('Class', 'Classanonymous', 'Trait'), Analyzer::WITHOUT_CONSTANTS)
              ->GoToAllParentsTraits(Analyzer::INCLUDE_SELF)
              ->outIs('PPP')
              ->outIs('PPP')
              ->atomIs(array('Propertydefinition', 'Virtualproperty'), Analyzer::WITHOUT_CONSTANTS)
              ->samePropertyAs('code', 'name', Analyzer::CASE_SENSITIVE)
              ->addETo('DEFINITION', 'property')
              ->returnCount();
        $query->prepareRawQuery();
        $result = $this->gremlin->query($query->getQuery(), $query->getArguments());
        $count = $result->toInt();

        // For normal method calls, in traits
        $query = $this->newQuery('SetClassPropertyRemoteDefinition member');
        $query->atomIs('Member', Analyzer::WITHOUT_CONSTANTS)
              ->_as('property')
              ->hasNoIn('DEFINITION')
              ->outIs('MEMBER')
              ->savePropertyAs('code', 'name')
              ->inIs('MEMBER')
              ->outIs('OBJECT')
              ->inIs('DEFINITION')
              ->atomIs(array('Class', 'Classanonymous', 'Trait'), Analyzer::WITHOUT_CONSTANTS)
              ->outIs('PPP')
              ->outIs('PPP')
              ->atomIs(array('Propertydefinition', 'Virtualproperty'), Analyzer::WITHOUT_CONSTANTS)
              ->samePropertyAs('propertyname', 'name', Analyzer::CASE_SENSITIVE)
              ->addETo('DEFINITION', 'property')
              ->returnCount();
        $query->prepareRawQuery();
        $result = $this->gremlin->query($query->getQuery(), $query->getArguments());
        $count += $result->toInt();

        display("Set $count property remote definitions");
    }
}

?>
