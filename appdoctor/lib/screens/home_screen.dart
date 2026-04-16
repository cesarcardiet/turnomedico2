import 'package:flutter/material.dart';
import '../theme/app_theme.dart';
import '../core/api/auth_repository.dart';
import '../core/api/patient_repository.dart';
import '../core/constants/api_constants.dart';
import '../core/network/api_client.dart';
import '../core/storage/token_storage.dart';

/// Home alineado con Figma: header, banner, categorías, lista de doctores.
class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final _searchController = TextEditingController();
  List<Map<String, dynamic>> _doctors = [];
  List<Map<String, dynamic>> _specialities = [];
  String? _userName;
  int? _selectedSpecialityId;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadUser();
    _loadSpecialities();
    _loadDoctors();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadUser() async {
    final user = await TokenStorage.getUser();
    if (!mounted) return;
    setState(() => _userName = user?['name']?.toString());
  }

  Future<void> _loadSpecialities() async {
    try {
      final list = await PatientRepository.getSpecialities();
      if (!mounted) return;
      setState(() {
        _specialities = list.map((e) => Map<String, dynamic>.from(e as Map)).toList();
      });
    } catch (_) {}
  }

  Future<void> _loadDoctors({bool refresh = false}) async {
    if (refresh) {
      setState(() {
        _error = null;
        _loading = true;
      });
    }
    try {
      final q = _searchController.text.trim();
      final data = await PatientRepository.getDoctors(
        page: 1,
        q: q.isEmpty ? null : q,
        specialityId: _selectedSpecialityId,
      );
      final list = data['data'] is List ? data['data'] as List : [];
      final items = list.map((e) => Map<String, dynamic>.from(e as Map)).toList();
      if (!mounted) return;
      setState(() {
        _doctors = items;
        _loading = false;
        _error = null;
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _loading = false;
        _error = e.message;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _loading = false;
        _error = 'Revisa tu conexión o el servidor.';
      });
    }
  }

  void _onSearch() => _loadDoctors(refresh: true);

  void _onCategoryTap(int? specialityId) {
    setState(() {
      _selectedSpecialityId = specialityId;
      _loading = true;
    });
    _loadDoctors(refresh: true);
  }

  Future<void> _onBookTap(BuildContext context) async {
    final loggedIn = await AuthRepository.isLoggedIn();
    if (!context.mounted) return;
    if (!loggedIn) await Navigator.of(context).pushNamed('/role');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: CustomScrollView(
          slivers: [
            _buildHeader(),
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 8, 20, 12),
                child: TextField(
                  controller: _searchController,
                  onSubmitted: (_) => _onSearch(),
                  decoration: InputDecoration(
                    hintText: 'Buscar un médico',
                    hintStyle: TextStyle(color: AppColors.gray, fontSize: 14),
                    prefixIcon: Icon(Icons.search, color: AppColors.gray),
                    suffixIcon: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.mic_none, color: AppColors.gray, size: 22),
                        const SizedBox(width: 8),
                      ],
                    ),
                    filled: true,
                    fillColor: AppColors.inputFill,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none,
                    ),
                  ),
                ),
              ),
            ),
            _buildBanner(),
            _buildCategories(),
            _buildAllDoctorsHeader(),
            if (_loading && _doctors.isEmpty)
              const SliverFillRemaining(
                child: Center(child: CircularProgressIndicator(color: AppColors.primary)),
              )
            else if (_error != null && _doctors.isEmpty)
              SliverFillRemaining(
                child: Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(_error!, textAlign: TextAlign.center, style: TextStyle(color: AppColors.red)),
                      const SizedBox(height: 16),
                      TextButton(onPressed: () => _loadDoctors(refresh: true), child: const Text('Reintentar')),
                    ],
                  ),
                ),
              )
            else
              SliverList(
                delegate: SliverChildBuilderDelegate(
                  (context, index) {
                    final d = _doctors[index];
                    final name = (d['user'] is Map ? (d['user'] as Map)['name'] : null)?.toString() ?? 'Doctor';
                    final spec = (d['speciality'] is Map ? (d['speciality'] as Map)['name'] : null)?.toString() ?? '—';
                    final ratingRaw = d['rating'];
                    final rating = ratingRaw is num
                        ? ratingRaw.toDouble()
                        : (double.tryParse(ratingRaw?.toString() ?? '') ?? 0.0);
                    final rawPhoto = (d['profile_photo_url'] ?? (d['user'] is Map ? (d['user'] as Map)['profile_photo_url'] : null))?.toString();
                    final photoUrl = ApiConstants.imageUrl(rawPhoto);
                    final about = (d['about']?.toString() ?? '').replaceAll(RegExp(r'\s+'), ' ').trim();
                    final id = d['id'];
                    return _DoctorCard(
                      id: id is int ? id : int.tryParse(id?.toString() ?? '0') ?? 0,
                      name: name,
                      specialty: spec,
                      about: about.isEmpty ? null : about,
                      rating: rating,
                      photoUrl: photoUrl,
                      onBook: () => _onBookTap(context),
                    );
                  },
                  childCount: _doctors.length,
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
        child: Row(
          children: [
            CircleAvatar(
              radius: 24,
              backgroundColor: AppColors.lightBlue,
              child: _userName != null && _userName!.isNotEmpty
                  ? Text(
                      _userName![0].toUpperCase(),
                      style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.w600, fontSize: 18),
                    )
                  : const Icon(Icons.person_outline, color: AppColors.primary),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Hola, bienvenido',
                    style: TextStyle(fontSize: 14, color: AppColors.gray),
                  ),
                  Text(
                    _userName ?? 'Invitado',
                    style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600, color: AppColors.black),
                  ),
                ],
              ),
            ),
            IconButton(
              icon: const Icon(Icons.notifications_outlined, color: AppColors.black),
              onPressed: () {},
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBanner() {
    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
        child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [AppColors.primary, AppColors.teal],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: AppColors.primary.withValues(alpha: 0.3),
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
        ),
          child: Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Centro médico',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: AppColors.white,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Encuentra médicos por especialidad y agenda tu cita de forma rápida y segura.',
                      style: TextStyle(fontSize: 13, color: AppColors.white.withValues(alpha: 0.9)),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 16),
              ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: Container(
                  width: 80,
                  height: 80,
                  color: AppColors.white.withValues(alpha: 0.2),
                  child: const Icon(Icons.medical_services, size: 48, color: AppColors.white),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildCategories() {
    // Si la API no devolvió especialidades, sacar las que vienen en los doctores (únicas por id)
    List<Map<String, dynamic>> unique = _specialities;
    if (unique.isEmpty && _doctors.isNotEmpty) {
      final seen = <int>{};
      unique = [];
      for (final d in _doctors) {
        final s = d['speciality'];
        if (s is! Map) continue;
        final id = s['id'];
        final idInt = id is int ? id : int.tryParse(id?.toString() ?? '');
        if (idInt != null && !seen.contains(idInt)) {
          seen.add(idInt);
          unique.add(Map<String, dynamic>.from(s));
        }
      }
    }

    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 24, 20, 12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Categorías',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600, color: AppColors.black),
                ),
                TextButton(
                  onPressed: () => _onCategoryTap(null),
                  child: const Text('Ver todo', style: TextStyle(color: AppColors.primary, fontWeight: FontWeight.w500)),
                ),
              ],
            ),
            const SizedBox(height: 12),
            SizedBox(
              height: 44,
              child: ListView(
                scrollDirection: Axis.horizontal,
                children: [
                  _CategoryChip(
                    label: 'Todos',
                    isSelected: _selectedSpecialityId == null,
                    onTap: () => _onCategoryTap(null),
                  ),
                  ...unique.map((s) {
                    final id = s['id'];
                    final idInt = id is int ? id : int.tryParse(id?.toString() ?? '');
                    final name = s['name']?.toString() ?? '—';
                    return _CategoryChip(
                      label: name,
                      isSelected: _selectedSpecialityId == idInt,
                      onTap: () => _onCategoryTap(idInt),
                    );
                  }),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildAllDoctorsHeader() {
    return SliverToBoxAdapter(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'Todos los médicos',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600, color: AppColors.black),
            ),
            TextButton(
              onPressed: _onSearch,
              child: const Text('Ver todo', style: TextStyle(color: AppColors.primary)),
            ),
          ],
        ),
      ),
    );
  }
}

class _CategoryChip extends StatelessWidget {
  final String label;
  final bool isSelected;
  final VoidCallback onTap;

  const _CategoryChip({required this.label, required this.isSelected, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(right: 10),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(12),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
            decoration: BoxDecoration(
              color: isSelected ? AppColors.teal : AppColors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: isSelected ? AppColors.teal : AppColors.inputBorder,
                width: 1.5,
              ),
            ),
            child: Center(
              child: Text(
                label,
                style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w600,
                  color: isSelected ? AppColors.white : AppColors.black,
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _DoctorAvatar extends StatelessWidget {
  final String? photoUrl;
  final String name;

  const _DoctorAvatar({this.photoUrl, required this.name});

  @override
  Widget build(BuildContext context) {
    final initial = name.isNotEmpty ? name[0].toUpperCase() : '?';
    if (photoUrl == null || photoUrl!.isEmpty) {
      return CircleAvatar(
        radius: 32,
        backgroundColor: AppColors.lightBlue,
        child: Text(
          initial,
          style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.w600, fontSize: 22),
        ),
      );
    }
    return ClipOval(
      child: Image.network(
        photoUrl!,
        width: 64,
        height: 64,
        fit: BoxFit.cover,
        loadingBuilder: (context, child, loadingProgress) {
          if (loadingProgress == null) return child;
          return Container(
            width: 64,
            height: 64,
            color: AppColors.lightBlue,
            child: const Center(child: SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2))),
          );
        },
        errorBuilder: (_, __, ___) => CircleAvatar(
          radius: 32,
          backgroundColor: AppColors.lightBlue,
          child: Text(
            initial,
            style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.w600, fontSize: 22),
          ),
        ),
      ),
    );
  }
}

class _DoctorCard extends StatelessWidget {
  final int id;
  final String name;
  final String specialty;
  final String? about;
  final double rating;
  final String? photoUrl;
  final VoidCallback onBook;

  const _DoctorCard({
    required this.id,
    required this.name,
    required this.specialty,
    this.about,
    required this.rating,
    this.photoUrl,
    required this.onBook,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 20, vertical: 6),
      elevation: 2,
      shadowColor: AppColors.primary.withValues(alpha: 0.08),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _DoctorAvatar(photoUrl: photoUrl, name: name),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    name,
                    style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 16, color: AppColors.black),
                  ),
                  Text(
                    specialty,
                    style: const TextStyle(fontSize: 14, color: AppColors.gray),
                  ),
                  if (about != null && about!.isNotEmpty) ...[
                    const SizedBox(height: 6),
                    Text(
                      about!.length > 80 ? '${about!.substring(0, 80)}...' : about!,
                      style: TextStyle(fontSize: 12, color: AppColors.gray),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      SizedBox(
                        height: 36,
                        child: ElevatedButton(
                          onPressed: onBook,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppColors.primary,
                            foregroundColor: AppColors.white,
                            padding: const EdgeInsets.symmetric(horizontal: 16),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          ),
                          child: const Text('Reservar'),
                        ),
                      ),
                      const Spacer(),
                      Icon(Icons.star, size: 18, color: AppColors.orange),
                      const SizedBox(width: 4),
                      Text(
                        rating > 0 ? rating.toStringAsFixed(1) : '—',
                        style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
                      ),
                      const SizedBox(width: 8),
                      IconButton(
                        icon: const Icon(Icons.favorite_border, color: AppColors.gray, size: 22),
                        onPressed: () {},
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
