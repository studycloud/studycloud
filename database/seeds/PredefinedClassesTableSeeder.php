<?php

use Illuminate\Database\Seeder;

class PredefinedClassesTableSeeder extends Seeder
{
	/**
	 * The number of fake classes to make
	 */
	const CLASS_ARRAY = array( 
        "Root" => array (
            "Core" => array (
                "Bio lab" => null,
                "Chem lab" => null,
                "Physics lab" => null,
                "STEMs" => null,
                "HSA" => array (
                    "Writ 1" => null,
                    "HSA 10" => null,
                    "Impact courses" => null,
                ),
                "CS" => array (
                    "CS 5" => null,
                    "CS 42" => null,
                    ),
                "Math" => array (
                    "Calculus" => null,
                    "Lin alg" => null,
                    ),
                "Physics" => array (
                    "Spec rel" => null,
                    "Mechanics" => null,
                    ),
                "Electives" => array (
                    "Math games" => null,
                    "Climate change" => null,
                    ),
                ),
            "CS" => array (
                "CS 60" => null,
                "CS 70" => null,
                "CS 81" => null,
                "Computer sys" => null,
                "Software dev" => null,
                "PLs" => null,
                "Algs" => null,
                ),
            "Math" => array (
                "Discrete" => null,
                "Prob and stats" => null,
                "Interm diff eq" => null,
                "Diff eq" => null,
                "Math 115" => null,
                "Analysis I" => null,
                "Interm prob" => null,
                "Abstract alg I" => null,
                "Intro partial diff eq" => null,
                ),
            "Physics" => array (
                "Quantum phys" => null,
                "Modern physics lab" => null,
                "Theo mech" => null,
                "Electronics lab" => null,
                "Quantum mech" => null,
                "Optics lab" => null,
                "EM fields" => null,
                ),
            "Engineering" => array (
                "E80s" => array (
                    "Experimental eng" => null,
                    "Chem & therm processes" => null,
                    "Continuum mech" => null,
                    "E & M circuits" => null,
                    "HM Digital electronics & computer engr" => null,
                    "HM Materials Eng" => null,
                    ),
                "Other required" => array (
                    "E4" => null,
                    "Intro sys eng" => null,
                    "Eng math" => null,
                    "Adv sys eng I" => null,
                    "Adv sys eng II" => null,
                    ),
                ),
            "Biology" => array (
                "Exp biol lab" => null,
                "Biostats" => null,
                "Comp phys" => null,
                "Ecology & env bio" => null,
                "Evo bio" => null,
                "Molec genetics" => null,
                "Adv math bio" => null,
                "Adv comp bio" => null,
                ),
            "MCBI" => array (
                "Intro math bio" => null,
                "Adv comp bio" => null,
                ),
            "Chemistry" => array (
                "Phys chem: thermodyn/kinetics" => null,
                "O chem I" => null,
                "O chem I lab" => null,
                "Chem analysis" => null,
                "Inorganic chem" => null,
                "O chem II" => null,
                "Biochem" => null,
                ),
            "Other tech" => array (
                "Astronomy" => null,
                "Geology" => null,
                "Neuroscience" => null,
                ),
            "HSA" => array(
                "Humanities (A - H)" => array(
                    "Anthropology" => null, 
                    "Cognitive Science" => null, 
                    "Env Analysis" => null,         
                    "Economics"  => null, 
                    "Government" => null, 
                    "GWS" => null, 
                    "History" => null, 
                ),
                "Humanities (H - S)" => array(
                    "Leadership" => null, 
                    "Linguistics" => null, 
                    "Literature" => null, 
                    "Philosophy" => null, 
                    "Politics" => null, 
                    "Psychology" => null, 
                    "Religious Studies" => null, 
                    "Sociology" => null, 
                ),
                "Arts" => array (
                    "Dance" => null,
                    "Theatre" => null,
                    "Music" => null,
                    "Visual Art" => null,
                    "Art History" => null,
                    "Art Conservation" => null,
                    "Media Studies" => null
                ),
                "Cultural studies" => array (
                    "ASAM" => null,
                    "AFRI" => null,
                    "AMST" => null,
                    "CHLT" => null,
                    "CHST" => null,
                    "GRMT" => null,
                    "PONT" => null,
                    "RUST" => null,
                    ),
                "Foreign Language" => array (
                    "Asia" => array(
                        "Japanese" => null,
                        "Korean" => null,
                        "Chinese" => null
                    ),
                    "Romance Lang" => array(
                        "French" => null,
                        "Italian" => null,
                        "Spanish" => null,
                        "Portuguese" => null
                    ),
                    "Classical Lang" => array(
                        "Greek" => null,
                        "Latin" => null
                    ),
                    "German" => null,
                    "Arabic" => null,
                    "Russian" => null,
                    ),
                "Other" => null,
            ),
        ));

    /**
     * Run the database seeds.
     *
     * @return void
     */
	public function run($run_with_fake_names = false)
	{
		$this->createClasses(null,  self::CLASS_ARRAY["Root"]);
	}

	/**
	 * Run the database seeds with names being numbered as "Item ".$id
	 */
	public function createClasses($parent_class, $children_array)
	{
        foreach($children_array as $class_name => $value) {
            // first element in the array is the current level's node name
            // create the class at the current level
            $curr_class = factory('App\Academic_Class')->create(['name' => $class_name]);
            
            if (!is_null($parent_class)){
                // set the parent
                $parent_class->children()->save($curr_class);
            }

            if (is_array($value)){
                // recursive if we are not at the base case
                $this->createClasses($curr_class, $value);
            }
        }
	}
}

?>