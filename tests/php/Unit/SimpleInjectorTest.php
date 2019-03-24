<?php

namespace ASMP\WordPressIntegration\Tests\Unit;

use ASMP\WordPressIntegration\Exception\FailedToMakeInstance;
use ASMP\WordPressIntegration\Infrastructure\Injector;
use ASMP\WordPressIntegration\Infrastructure\Injector\SimpleInjector;
use ASMP\WordPressIntegration\Tests\Fixture;

final class SimpleInjectorTest extends TestCase {

	public function test_it_can_be_initialized(): void {
		$injector = new SimpleInjector();

		$this->assertInstanceOf( SimpleInjector::class, $injector );
	}

	public function test_it_implements_the_interface(): void {
		$injector = new SimpleInjector();

		$this->assertInstanceOf( Injector::class, $injector );
	}

	public function test_it_can_instantiate_a_concrete_class(): void {
		$object = ( new SimpleInjector() )
			->make( Fixture\DummyClass::class );

		$this->assertInstanceOf( Fixture\DummyClass::class, $object );
	}

	public function test_it_can_autowire_a_class_with_a_dependency(): void {
		$object = ( new SimpleInjector() )
			->make( Fixture\DummyClassWithDependency::class );

		$this->assertInstanceOf( Fixture\DummyClassWithDependency::class, $object );
		$this->assertInstanceOf( Fixture\DummyClass::class, $object->get_dummy() );
	}

	public function test_it_can_instantiate_a_bound_interface(): void {
		$injector = ( new SimpleInjector() )
			->bind(
				Fixture\DummyInterface::class,
				Fixture\DummyClassWithDependency::class
			);
		$object = $injector->make( Fixture\DummyInterface::class );

		$this->assertInstanceOf( Fixture\DummyInterface::class, $object );
		$this->assertInstanceOf( Fixture\DummyClassWithDependency::class, $object );
		$this->assertInstanceOf( Fixture\DummyClass::class, $object->get_dummy() );
	}

	public function test_it_returns_separate_instances_by_default(): void {
		$injector = new SimpleInjector();
		$object_a = $injector->make( Fixture\DummyClass::class );
		$object_b = $injector->make( Fixture\DummyClass::class );

		$this->assertNotSame( $object_a, $object_b );
	}

	public function test_it_returns_same_instances_if_shared(): void {
		$injector = ( new SimpleInjector() )
			->share( Fixture\DummyClass::class );
		$object_a = $injector->make( Fixture\DummyClass::class );
		$object_b = $injector->make( Fixture\DummyClass::class );

		$this->assertSame( $object_a, $object_b );
	}

	public function test_it_can_instantiate_a_class_with_named_arguments(): void {
		$object = ( new SimpleInjector() )
			->make(
				Fixture\DummyClassWithNamedArguments::class,
				[ 'argument_a' => 42, 'argument_b' => 'Mr Alderson' ]
			);

		$this->assertInstanceOf( Fixture\DummyClassWithNamedArguments::class, $object );
		$this->assertEquals( 42, $object->get_argument_a() );
		$this->assertEquals( 'Mr Alderson', $object->get_argument_b() );
	}

	public function test_it_allows_for_skipping_named_arguments_with_default_values(): void {
		$object = ( new SimpleInjector() )
			->make(
				Fixture\DummyClassWithNamedArguments::class,
				[ 'argument_a' => 42 ]
			);

		$this->assertInstanceOf( Fixture\DummyClassWithNamedArguments::class, $object );
		$this->assertEquals( 42, $object->get_argument_a() );
		$this->assertEquals( 'Mr Meeseeks', $object->get_argument_b() );
	}

	public function test_it_throws_if_a_required_named_arguments_is_missing(): void {
		$this->expectException( FailedToMakeInstance::class );

		( new SimpleInjector() )
			->make( Fixture\DummyClassWithNamedArguments::class );
	}

	public function test_it_throws_if_a_circular_reference_is_detected(): void {
		$this->expectException( FailedToMakeInstance::class );
		$this->expectExceptionCode( FailedToMakeInstance::CIRCULAR_REFERENCE );

		( new SimpleInjector() )
			->bind(
				Fixture\DummyClass::class,
				Fixture\DummyClassWithDependency::class
			)
			->make( Fixture\DummyClassWithDependency::class );
	}
}
