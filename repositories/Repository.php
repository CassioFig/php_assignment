<?php
	abstract class Repository {
		function create($data) {}
		function find($id) {}
		function update($id, $data) {}
		function delete($id) {}
		function all() {}
	}